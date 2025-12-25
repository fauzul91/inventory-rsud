<?php

namespace App\Services\V1;

use App\Exceptions\BusinessException;
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
    private NotifikasiService $notifikasiService;
    private DetailBarangService $detailBarangService;
    private DetailPegawaiService $detailPegawaiService;

    public function __construct(
        PenerimaanRepository $repository,
        StokService $stokService,
        MonitoringService $monitoringService,
        NotifikasiService $notifikasiService,
        DetailBarangService $detailBarangService,
        DetailPegawaiService $detailPegawaiService
    ) {
        $this->repository = $repository;
        $this->stokService = $stokService;
        $this->monitoringService = $monitoringService;
        $this->notifikasiService = $notifikasiService;
        $this->detailBarangService = $detailBarangService;
        $this->detailPegawaiService = $detailPegawaiService;
    }
    /**
     * Ambil list Penerimaan dengan filter, status, dan context
     * @param array $filters
     * @param array|null $statuses
     * @param string $context
     */
    public function getPenerimaanList(array $filters = [], array $statuses = null, string $context = 'default')
    {
        $penerimaanData = $this->repository->getPenerimaanForTable($filters, $statuses);
        return $this->transformPenerimaan($penerimaanData, $context);
    }
    private function transformPenerimaan($data, $context = 'default', $filterStatuses = null)
    {
        $collection = $data->getCollection();

        if ($filterStatuses) {
            $collection = $collection->whereIn('status', $filterStatuses)->values();
        }

        $transformed = $collection->map(function ($item) use ($context) {
            return [
                'id' => $item->id,
                'no_surat' => $item->no_surat,
                'role_user' => $item->user->roles->pluck('name')->first() ?? null,
                'category_name' => $item->category->name ?? null,
                'pegawai_name' => optional($item->detailPegawai->first()?->pegawai)->name,
                'status' => $this->mapPenerimaanStatusLabel($item->status, $context),
                'status_code' => $item->status,
            ];
        });

        $data->setCollection($transformed);
        return $data;
    }
    private function mapPenerimaanStatusLabel(string $status, string $context = 'default'): string
    {
        return match ($context) {
            'check' => match ($status) {
                    'pending' => 'Belum Dicek',
                    'checked' => 'Sedang Dicek',
                    default => 'Telah Dicek',
                },
            'signed' => match ($status) {
                    'confirmed' => 'Belum Ditandatangani',
                    'signed' => 'Telah Ditandatangani',
                    default => 'Telah Ditandatangani',
                },
            'paid' => match ($status) {
                    'signed' => 'Belum Dibayar',
                    'paid' => 'Sudah Dibayar',
                    default => 'Sudah Dibayar',
                },
            default => match ($status) {
                    'pending' => 'Belum Dicek',
                    'checked' => 'Sedang Dicek',
                    'confirmed' => 'Telah Dikonfirmasi',
                    'signed' => 'Sudah Ditandatangani',
                    'paid' => 'Sudah Dibayar',
                    default => 'Status Tidak Diketahui',
                },
        };
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
            $this->notifikasiService->penerimaanDiajukan($penerimaan, Auth::user()->name ?? "Tim PPK");
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


    public function updatePenerimaan($penerimaanId, array $data)
    {
        return DB::transaction(function () use ($penerimaanId, $data) {
            $penerimaan = $this->repository->findById($penerimaanId);

            if (!$penerimaan) {
                throw new \Exception("Penerimaan dengan ID {$penerimaanId} tidak ditemukan");
            }

            if ($penerimaan->status !== 'pending') {
                throw new \Exception("Penerimaan dengan status '{$penerimaan->status}' tidak bisa diupdate.");
            }

            $categoryId = $penerimaan->category_id;
            if (isset($data['category_id']) || isset($data['category_name'])) {
                $category = $this->findOrCreateCategory($data);
                $categoryId = $category->id;
            }

            $updateData = [];

            if (isset($data['no_surat'])) {
                $updateData['no_surat'] = $data['no_surat'];
            }

            if (isset($data['deskripsi'])) {
                $updateData['deskripsi'] = $data['deskripsi'];
            }

            if (isset($categoryId) && $categoryId !== $penerimaan->category_id) {
                $updateData['category_id'] = $categoryId;
            }

            if (isset($data['status'])) {
                $updateData['status'] = $data['status'];
            }

            if (!empty($updateData)) {
                $penerimaan = $this->repository->update($penerimaan, $updateData);
            }

            if (isset($data['detail_barangs'])) {
                $this->detailBarangService->syncDetailBarang(
                    $penerimaan->fresh(),
                    $data['detail_barangs']
                );
            }

            if (isset($data['pegawais'])) {
                $this->detailPegawaiService->syncDetailPegawai(
                    $penerimaan,
                    $data['pegawais']
                );
            }

            $this->monitoringService->log(
                "Memperbarui penerimaan: {$penerimaan->no_surat}",
                Auth::id() ?? 4
            );

            return $penerimaan->fresh()->load([
                'detailBarang.stok.category',
                'detailBarang.stok.satuan',
                'detailPegawai.pegawai.jabatan',
                'category'
            ]);
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
                throw new BusinessException('Detail barang tidak ditemukan.', 404);
            }

            $penerimaan = $this->repository->findById($penerimaanId);
            if (!in_array($penerimaan->status, ['pending', 'checked'])) {
                throw new BusinessException(
                    'Kelayakan hanya bisa diupdate pada status pending atau checked',
                    422
                );
            }

            if ($detail->is_layak === true && $data['is_layak'] === false) {
                throw new BusinessException(
                    'Barang yang sudah dinyatakan layak tidak dapat dibatalkan.',
                    422
                );
            }

            if ($penerimaan->status === 'pending') {
                $this->repository->update($penerimaan, [
                    'status' => 'checked'
                ]);
            }

            $this->repository->updateDetailBarang($detail, [
                'is_layak' => $data['is_layak']
            ]);

            $this->monitoringService->log(
                "Menilai kelayakan barang: {$detail->stok->name}",
                4
            );

            return $detail->fresh();
        });
    }
    public function markDetailAsPaid($penerimaanId, $detailId)
    {
        return DB::transaction(function () use ($penerimaanId, $detailId) {
            $detail = $this->repository->findDetailBarang($penerimaanId, $detailId);

            if (!$detail) {
                throw new BusinessException('Detail barang tidak ditemukan.', 404);
            }

            if ($detail->is_paid === true) {
                throw new BusinessException(
                    'Detail barang ini sudah dibayar sebelumnya.',
                    422
                );
            }

            $detail = $this->repository->updateDetailBarangPayment($detail);
            $allDetails = $this->repository->getAllDetailBarang($penerimaanId);

            $total = $allDetails->count();
            $paid = $allDetails->where('is_paid', true)->count();

            if ($total > 0 && $total === $paid) {
                $this->repository->updatePenerimaanStatus($penerimaanId, 'paid');
            }

            $penerimaan = $this->repository->findById($penerimaanId);

            return [
                'success' => true,
                'message' => 'Detail berhasil ditandai sebagai paid',
                'data' => $penerimaan
            ];
        });
    }
    public function confirmPenerimaan($id)
    {
        return DB::transaction(function () use ($id) {
            $penerimaan = $this->repository->findWithDetails($id);
            if (!$penerimaan) {
                return [
                    'success' => false,
                    'message' => 'Penerimaan tidak ditemukan'
                ];
            }
            if ($penerimaan->status !== 'checked') {
                return [
                    'success' => false,
                    'message' => 'Penerimaan belum siap untuk dikonfirmasi'
                ];
            }
            if ($this->repository->hasUnassessedItems($id)) {
                return [
                    'success' => false,
                    'message' => 'Masih ada barang yang belum dinilai kelayakannya'
                ];
            }
            if ($this->repository->hasUnfitItems($id)) {
                return [
                    'success' => false,
                    'message' => 'Masih terdapat barang yang tidak layak'
                ];
            }
            $this->repository->update($penerimaan, [
                'status' => 'confirmed'
            ]);

            $this->monitoringService->log(
                "Mengkonfirmasi penerimaan: {$penerimaan->no_surat}",
                2
            );
            $this->notifikasiService->uploadTTDPenerimaan($penerimaan, Auth::user()->name ?? "Tim Teknis");

            return [
                'success' => true,
                'data' => $penerimaan->fresh()
            ];
        });
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
                'is_paid' => $item->is_paid,
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