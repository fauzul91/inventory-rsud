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
        $year = $filters['year'] ?? now()->year;

        $query = $this->stokRepository->getAllStoks($filters);
        $query->with([
            'category:id,name',
            'satuan:id,name',
            'detailPenerimaanBarang' => fn($q) =>
                $q->where('is_layak', true)
                    ->whereHas(
                        'penerimaan',
                        fn($p) =>
                        $p->whereIn('status', ['checked', 'confirmed', 'signed', 'paid'])
                            ->whereYear('created_at', '<=', $year)
                    )
        ])->orderBy('name');

        $stoks = $query->paginate($perPage);
        $stoks->getCollection()->transform(function ($stok) use ($year) {

            $details = $stok->detailPenerimaanBarang;

            $masukThisYear = $details
                ->filter(fn($d) => $d->penerimaan->created_at->year == $year)
                ->sum('quantity');

            $masukBefore = $details
                ->filter(fn($d) => $d->penerimaan->created_at->year < $year)
                ->sum('quantity');

            $keluarTotal = $details
                ->flatMap(fn($d) => $d->detailPemesanans)
                ->sum('pivot.quantity');

            return [
                'id' => $stok->id,
                'name' => $stok->name,
                'category_name' => $stok->category->name,
                'stok_lama' => $masukBefore,
                'stok_masuk' => $masukThisYear,
                'stok_keluar' => $keluarTotal,
                'total_stok' => ($masukBefore + $masukThisYear) - $keluarTotal,
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
                'status' => ($isPaid || $item->status === 'paid')
                    ? 'Telah Dibayar'
                    : 'Belum Dibayar',
            ];
        });

        $data->setCollection($transformed);
        return $data;
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