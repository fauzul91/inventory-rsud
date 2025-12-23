<?php

namespace App\Services\V1;

use App\Models\StokHistory;
use App\Repositories\V1\StokRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $stoks = $this->stokRepository
            ->getAllStoks($filters)
            ->paginate($perPage);

        $stoks->getCollection()->transform(function ($stok) {
            $details = $stok->detailPenerimaanBarang;
            $stokMasuk = $details->sum('quantity');
            $stokKeluar = $details
                ->flatMap(fn($d) => $d->detailPemesanans)
                ->sum('pivot.quantity');
            $totalStok = $stokMasuk - $stokKeluar;
            return [
                'id' => $stok->id,
                'name' => $stok->name,
                'category_name' => $stok->category->name,
                'stok_masuk' => $stokMasuk,
                'stok_keluar' => $stokKeluar,
                'total_stok' => $totalStok,
                'minimum_stok' => $stok->minimum_stok,
                'satuan' => $stok->satuan->name ?? null,
            ];
        });

        return $stoks;
    }
    public function getStockById($id)
    {
        return $this->stokRepository->getStokById($id);
    }
    public function edit($id)
    {
        return $this->stokRepository->edit($id);
    }
    public function update($data, $id)
    {
        return $this->stokRepository->update($data, $id);
    }
}