<?php

namespace App\Services\V1;

use App\Repositories\V1\PenerimaanRepository;
use App\Models\Stok;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PenerimaanService
{
    private PenerimaanRepository $repository;
    private MonitoringService $monitoringService;
    private DetailBarangService $detailBarangService;
    private DetailPegawaiService $detailPegawaiService;

    public function __construct(
        PenerimaanRepository $repository,
        MonitoringService $monitoringService,
        DetailBarangService $detailBarangService,
        DetailPegawaiService $detailPegawaiService
    ) {
        $this->repository = $repository;
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
            $penerimaan = $this->repository->create([
                'user_id' => Auth::id() ?? 4,
                'no_surat' => $data['no_surat'],
                'category_id' => $data['category_id'],
                'deskripsi' => $data['deskripsi'] ?? null,
                'status' => 'pending',
            ]);

            if (!empty($data['detail_barangs'])) {
                $this->detailBarangService->createMultiple($penerimaan->id, $data['detail_barangs']);
            }

            if (!empty($data['pegawais'])) {
                $this->detailPegawaiService->createMultiple($penerimaan->id, $data['pegawais']);
            }

            $this->monitoringService->log("Membuat penerimaan: {$penerimaan->no_surat}", 4);

            return $penerimaan->load(['detailBarang', 'detailPegawai.pegawai']);
        });
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

    public function markBarangLayak($penerimaanId, $detailId, $isLayak)
    {
        $detail = $this->repository->findDetailBarang($penerimaanId, $detailId);

        if (!$detail) {
            return [
                'success' => false,
                'message' => 'Detail barang tidak ditemukan untuk penerimaan ini.'
            ];
        }

        $this->repository->updateDetailBarang($detail, ['is_layak' => (bool) $isLayak]);

        return [
            'success' => true,
            'data' => $detail
        ];
    }

    public function confirmPenerimaan($id)
    {
        $penerimaan = $this->repository->findWithDetails($id);

        if ($this->repository->hasUnassessedItems($id)) {
            return [
                'success' => false,
                'message' => 'Masih ada barang yang belum dinilai kelayakannya'
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
                'total_harga' => $item->total_harga,
                'is_layak' => $item->is_layak,
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