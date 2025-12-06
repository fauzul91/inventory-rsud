<?php

namespace App\Services\V1;

use App\Models\Category;
use App\Repositories\V1\PenerimaanRepository;
use App\Models\Stok;
use App\Repositories\V1\StokRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PenerimaanService
{
    private PenerimaanRepository $repository;
    private StokService $stokService;
    private MonitoringService $monitoringService;
    private DetailBarangService $detailBarangService;
    private DetailPegawaiService $detailPegawaiService;

    public function __construct(
        PenerimaanRepository $repository,
        StokService $stokService,
        MonitoringService $monitoringService,
        DetailBarangService $detailBarangService,
        DetailPegawaiService $detailPegawaiService
    ) {
        $this->repository = $repository;
        $this->stokService = $stokService;
        $this->monitoringService = $monitoringService;
        $this->detailBarangService = $detailBarangService;
        $this->detailPegawaiService = $detailPegawaiService;
    }

    public function getAllPenerimaan(array $filters)
    {
        return $this->repository->getAllPenerimaan($filters);
    }

    public function getHistoryPenerimaan(array $filters)
    {
        return $this->repository->getHistoryPenerimaan($filters);
    }

    public function getPenerimaanForEdit($id)
    {
        $penerimaan = $this->repository->findById($id);

        return [
            'id' => $penerimaan->id,
            'no_surat' => $penerimaan->no_surat,
            'deskripsi' => $penerimaan->deskripsi,
            'status' => $penerimaan->status,
            'category' => [
                'id' => $penerimaan->category->id,
                'name' => $penerimaan->category->name
            ],
            'detail_barang' => $this->transformDetailBarang($penerimaan->detailBarang),
            'detail_pegawai' => $this->transformDetailPegawai($penerimaan->detailPegawai)
        ];
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $category = $this->findOrCreateCategory($data);

            $penerimaan = $this->repository->create([
                'user_id' => Auth::id() ?? 4,
                'no_surat' => $data['no_surat'],
                'category_id' => $category->id,
                'deskripsi' => $data['deskripsi'] ?? null,
                'status' => 'pending',
            ]);

            if (!empty($data['detail_barangs'])) {
                $this->detailBarangService->createMultiple($penerimaan->id, $data['detail_barangs'], $category->id);
            }

            if (!empty($data['pegawais'])) {
                $this->detailPegawaiService->createMultiple($penerimaan->id, $data['pegawais']);
            }

            $this->monitoringService->log("Membuat penerimaan: {$penerimaan->no_surat}", 4);

            return $penerimaan->load(['detailBarang', 'detailPegawai.pegawai']);
        });
    }
    private function findOrCreateCategory(array $barang)
    {
        if (!empty($barang['category_id'])) {
            $category = Category::find($barang['category_id']);
            if ($category)
                return $category;
        }
        if (!empty($barang['category_name'])) {
            $nameToSearch = ucfirst($barang['category_name']);
            $existingCategory = Category::whereRaw('LOWER(name) = ?', [$nameToSearch])->first();

            if ($existingCategory) {
                return $existingCategory;
            }
            return Category::create(['name' => $nameToSearch]);
        }
        return Category::firstOrCreate(['name' => 'Lainnya']);
    }

    public function update(array $data, $id)
    {
        return DB::transaction(function () use ($data, $id) {
            $penerimaan = $this->repository->findById($id);

            $updateFields = $this->prepareUpdateFields($data);
            if (!empty($updateFields)) {
                $this->repository->update($penerimaan, $updateFields);
            }

            if (isset($data['detail_barangs'])) {
                $this->detailBarangService->syncDetailBarang(
                    $penerimaan,
                    $data['detail_barangs']
                );
            }

            if (!empty($data['deleted_barang_ids'])) {
                $this->repository->deleteDetailBarang($data['deleted_barang_ids']);
            }

            if (isset($data['pegawais'])) {
                $this->detailPegawaiService->syncDetailPegawai(
                    $penerimaan->id,
                    $data['pegawais']
                );
            }

            $this->monitoringService->log("Mengupdate penerimaan: {$penerimaan->no_surat}", 4);

            return $penerimaan->fresh()->load(['detailBarang', 'detailPegawai.pegawai', 'category']);
        });
    }

    public function delete($id)
    {
        $penerimaan = $this->repository->findById($id);

        $penerimaan->detailBarang()->delete();
        $penerimaan->detailPegawai()->delete();

        $this->monitoringService->log("Menghapus penerimaan: {$penerimaan->no_surat}", 4);

        return $this->repository->delete($penerimaan);
    }

    public function updateKelayakanBarang($penerimaanId, $detailId, array $data)
    {
        return DB::transaction(function () use ($penerimaanId, $detailId, $data) {
            $detail = $this->repository->findDetailBarang($penerimaanId, $detailId);
            if (!$detail) {
                return [
                    'success' => false,
                    'message' => 'Detail barang tidak ditemukan.'
                ];
            }

            $penerimaan = $this->repository->findById($penerimaanId);

            if (!in_array($penerimaan->status, ['pending', 'checked'])) {
                return [
                    'success' => false,
                    'message' => 'Kelayakan barang hanya bisa diupdate di status pending atau checked'
                ];
            }

            $quantityLayak = $data['quantity_layak'];

            if ($quantityLayak < 0 || $quantityLayak > $detail->quantity) {
                return [
                    'success' => false,
                    'message' => "Jumlah layak harus antara 0 dan {$detail->quantity}"
                ];
            }
            $previousLayak = $detail->quantity_layak ?? 0;
            $change = $quantityLayak - $previousLayak;
            if ($penerimaan->status === 'pending') {
                $this->repository->update($penerimaan, [
                    'status' => 'checked'
                ]);
            }

            $detail = $this->repository->updateDetailBarang($detail, [
                'quantity_layak' => $quantityLayak,
                'quantity_tidak_layak' => $detail->quantity - $quantityLayak,
            ]);

            if ($change > 0) {
                $this->stokService->tambahStok(
                    $detail->stok_id,
                    $change,
                    'penerimaan',
                    $penerimaan->id
                );
            }

            if ($change < 0) {
                return [
                    'success' => false,
                    'message' => 'Anda tidak dapat mengurangi jumlah barang layak yang sudah ditetapkan sebelumnya. Gunakan proses Pengurangan Stok (Adjustment) jika barang harus dikeluarkan.'
                ];
            }

            $this->monitoringService->log(
                "Menilai kelayakan barang: {$detail->stok->name}",
                4
            );

            return [
                'success' => true,
                'data' => $detail,
                'message' => 'Kelayakan barang berhasil dinilai'
            ];
        });
    }
    public function markDetailAsPaid($penerimaanId, $detailId)
    {
        return DB::transaction(function () use ($penerimaanId, $detailId) {

            $detail = $this->repository->findDetailBarang($penerimaanId, $detailId);

            if (!$detail) {
                return [
                    'success' => false,
                    'message' => 'Detail barang tidak ditemukan di penerimaan ini.'
                ];
            }

            if ($detail->is_paid) {
                return [
                    'success' => false,
                    'message' => 'Detail barang ini sudah dibayar sebelumnya.'
                ];
            }

            $detail = $this->repository->updateDetailBarangPayment($detail);
            return $detail;
        });
    }
    public function confirmPenerimaan($id)
    {
        $penerimaan = $this->repository->findWithDetails($id);

        if ($this->repository->hasUnassessedItems($id)) {
            $unassessedCount = $this->repository->getUnassessedCount($id);
            return [
                'success' => false,
                'message' => "Masih ada {$unassessedCount} barang yang belum dinilai kelayakannya"
            ];
        }

        if ($this->repository->hasUnverifiedItems($id)) {
            return [
                'success' => false,
                'message' => 'Tidak bisa dikonfirmasi. Masih ada barang yang tidak layak. Silakan update penerimaan agar semua barang layak.'
            ];
        }

        $this->repository->update($penerimaan, ['status' => 'confirmed']);
        $this->monitoringService->log("Mengkonfirmasi penerimaan: {$penerimaan->no_surat}", 2);

        return [
            'success' => true,
            'data' => $penerimaan->fresh()
        ];
    }

    private function prepareUpdateFields(array $data)
    {
        $updateFields = [];
        $allowedFields = ['no_surat', 'category_id', 'deskripsi', 'status'];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[$field] = $data[$field];
            }
        }

        return $updateFields;
    }

    private function transformDetailBarang($detailBarang)
    {
        return $detailBarang->map(function ($item) {
            return [
                'id' => $item->id,
                'stok_id' => $item->stok_id,
                'nama_stok' => $item->stok->name,
                'nama_category' => $item->stok->category->name,
                'nama_satuan' => $item->stok->satuan->name,
                'harga' => $item->harga,
                'quantity' => $item->quantity,
                'quantity_layak' => $item->quantity_layak,
                'quantity_tidak_layak' => $item->quantity_tidak_layak,
                'total_harga' => $item->total_harga,
                'is_checked' => !is_null($item->quantity_layak),
                'is_all_layak' => $item->quantity === $item->quantity_layak,
                'is_paid' => (bool) $item->is_paid,
            ];
        });
    }

    private function transformDetailPegawai($detailPegawai)
    {
        return $detailPegawai->map(function ($item) {
            return [
                'id' => $item->pegawai->id,
                'name' => $item->pegawai->name,
                'nip' => $item->pegawai->nip,
                'jabatan_id' => $item->pegawai->jabatan->id,
                'jabatan_name' => $item->pegawai->jabatan->name,
                'alamat_satker' => $item->alamat_staker
            ];
        });
    }
}