<?php

namespace App\Services\V1;

use App\Repositories\V1\StokRepository;
use Illuminate\Http\Request;

class StokService
{
    private StokRepository $stokRepository;
    private DetailBarangService $detailBarangService;
    private DetailPegawaiService $detailPegawaiService;

    public function __construct(
        StokRepository $stokRepository,
        MonitoringService $monitoringService,
        DetailBarangService $detailBarangService,
        DetailPegawaiService $detailPegawaiService
    ) {
        $this->stokRepository = $stokRepository;
        $this->monitoringService = $monitoringService;
        $this->detailBarangService = $detailBarangService;
        $this->detailPegawaiService = $detailPegawaiService;
    }
    public function getAllYearForSelect()
    {
        return $this->stokRepository->getAllYearForSelect();
    }
    public function getAllStoks(array $filters)
    {
        $perPage = $filters['per_page'] ?? 10;
        $year = $filters['year'];

        $stoks = $this->stokRepository->getAllStoks($filters)
            ->whereHas('histories', fn($q) => $q->where('year', $year))
            ->paginate($perPage);

        $stoks->getCollection()->transform(function ($stok) use ($year) {
            return [
                'name' => $stok->name,
                'category_name' => $stok->category->name,
                'stok_lama' => $stok->histories
                    ->where('year', '<', $year)
                    ->sum('remaining_qty'),
                'total_stok' => $stok->histories
                    ->where('year', $year)
                    ->sum('remaining_qty'),
                'minimum_stok' => $stok->minimum_stok,
                'satuan' => $stok->satuan->name ?? null,
                'price' => $stok->price,
            ];
        });

        return $stoks;
    }
    public function getPaidBastStock(array $filters)
    {
        $perPage = $filters['per_page'] ?? 10;
        $stoks = $this->stokRepository->getPaidBastStock($filters)->paginate($perPage);
        $transforms = $this->transformBastSTock($stoks, true);

        return $transforms;
    }
    public function getUnpaidBastStock(array $filters)
    {
        $perPage = $filters['per_page'] ?? 10;
        $stoks = $this->stokRepository->getUnpaidBastStock($filters)->paginate($perPage);
        $transforms = $this->transformBastSTock($stoks, false);

        return $transforms;
    }
    private function transformBastSTock($data, $isPaid = false)
    {
        $transformed = $data->getCollection()->map(function ($item) use ($isPaid) {
            return [
                'id' => $item->id,
                'no_surat' => $item->no_surat,
                'role_user' => $item->user->roles->pluck('name')->first() ?? null,
                'category_name' => $item->category->name ?? null,
                'pegawai_name' => optional($item->detailPegawai->first()->pegawai)->name ?? null,
                'status' => $isPaid ? 'Telah Dibayar' :
                    ($item->status === 'confirmed' ? 'Belum Dibayar' : 'Telah Dibayar'),
            ];
        });

        $data->setCollection($transformed);
        return $data;
    }
}