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
            $detailsThisYear = $stok->detailPenerimaanBarang
                ->filter(fn($d) => $d->penerimaan->created_at->year == $year);
            $detailsBefore = $stok->detailPenerimaanBarang
                ->filter(fn($d) => $d->penerimaan->created_at->year < $year);

            return [
                'id' => $stok->id,
                'name' => $stok->name,
                'category_name' => $stok->category->name,
                'stok_lama' => $detailsBefore->sum('quantity'),
                'total_stok' => $detailsThisYear->sum('quantity'),
                'minimum_stok' => $stok->minimum_stok,
                'satuan' => $stok->satuan->name ?? null,
            ];
        });

        return $stoks;
    }
    public function getStockById($id)
    {
        $stok = $this->stokRepository->getStokById($id);

        return [
            'id' => $stok->id,
            'name' => $stok->name,
            'category_name' => $stok->category?->name,
            'satuan' => $stok->satuan?->name,
            'minimum_stok' => $stok->minimum_stok,
            'riwayat_penerimaan' => $stok->detailPenerimaanBarang->map(function ($detail) {
                return [
                    'penerimaan_id' => $detail->penerimaan->id,
                    'no_surat' => $detail->penerimaan->no_surat,
                    'quantity' => $detail->quantity,
                    'harga' => $detail->harga,
                    'total_harga' => $detail->total_harga,
                    'status' => $detail->penerimaan->status,
                    'created_at' => $detail->penerimaan->created_at->toISOString(),
                ];
            }),
        ];
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
    public function tambahStok($stokId, $jumlah, $source = 'penerimaan', $sourceId = null)
    {
        return DB::transaction(function () use ($stokId, $jumlah, $source, $sourceId) {
            $year = Carbon::now()->year;
            $history = StokHistory::firstOrCreate(
                ['stok_id' => $stokId, 'year' => $year],
                [
                    'quantity' => 0,
                    'used_qty' => 0,
                    'remaining_qty' => 0,
                ]
            );

            $history->quantity += $jumlah;
            $history->remaining_qty = $history->quantity - $history->used_qty;
            $history->source = $source;
            $history->source_id = $sourceId;
            $history->save();

            return $history;
        });
    }

    public function kurangStok($stokId, $qty, $source = 'adjustment', $sourceId = null)
    {
        $year = Carbon::now()->year;

        $history = StokHistory::where('stok_id', $stokId)
            ->where('year', $year)
            ->first();
        if (!$history) {
            throw new \Exception("Stok tahun ini belum ada, tidak bisa mengurangi stok.");
        }
        if ($history->remaining_qty < $qty) {
            throw new \Exception("Stok tidak mencukupi untuk dikurangi.");
        }

        $history->used_qty += $qty;
        $history->remaining_qty -= $qty;
        $history->source = $source;
        $history->source_id = $sourceId;
        $history->save();
        return $history;
    }
}