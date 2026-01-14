<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\StokRepositoryInterface;
use App\Models\Penerimaan;
use App\Models\Stok;
use DB;

class StokRepository implements StokRepositoryInterface
{
    public function getAllStoksForSelect($categoryId = null)
    {
        $query = Stok::with('satuan:id,name')
            ->select('id', 'name', 'satuan_id');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return $query->orderBy('name', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'satuan_id' => $item->satuan_id,
                    'satuan_name' => $item->satuan->name ?? null,
                ];
            });
    }
    public function getAllYearForSelect()
    {
        return Penerimaan::query()
            ->whereIn('status', ['checked', 'confirmed', 'signed', 'paid'])
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderBy('year')
            ->get()
            ->map(fn($row) => [
                'label' => (string) $row->year,
                'value' => $row->year,
            ]);
    }
    public function getAllStoks(array $filters)
    {
        return Stok::query()
            ->when(
                $filters['search'] ?? null,
                fn($q, $v) =>
                $q->where('name', 'like', "%{$v}%")
            )
            ->when(
                $filters['category'] ?? null,
                fn($q, $v) =>
                $q->where('category_id', $v)
            )
            ->with([
                'category:id,name',
                'satuan:id,name',
                'detailPenerimaanBarang' => fn($q) =>
                    $q->where('is_layak', true)
                        ->whereHas(
                            'penerimaan',
                            fn($p) =>
                            $p->whereIn('status', ['confirmed', 'signed', 'paid'])
                        )
            ])
            ->orderBy('name');
    }
    public function getStokById($stokId)
    {
        $stok = Stok::with([
            'category:id,name',
            'satuan:id,name',
        ])->findOrFail($stokId);

        $validStatuses = ['confirmed', 'signed', 'paid'];
        $validStatusesKeluar = ['approved_admin_gudang'];
        $masuk = DB::table('detail_penerimaan_barangs as dpb')
            ->join('penerimaans as p', 'dpb.penerimaan_id', '=', 'p.id')
            ->where('dpb.stok_id', $stokId)
            ->where('dpb.is_layak', true)
            ->whereIn('p.status', $validStatuses)
            ->select([
                'dpb.created_at as tanggal',
                DB::raw("'masuk' as tipe"),
                DB::raw("CONCAT('BAST-', p.no_surat) as no_surat"),
                'dpb.quantity',
                'dpb.harga',
                'dpb.total_harga',
            ]);

        $keluar = DB::table('detail_pemesanan_penerimaan as dpp')
            ->join(
                'detail_penerimaan_barangs as dpb',
                'dpp.detail_penerimaan_id',
                '=',
                'dpb.id'
            )
            ->join(
                'penerimaans as p',
                'dpb.penerimaan_id',
                '=',
                'p.id'
            )
            ->join(
                'detail_pemesanans as dps',
                'dpp.detail_pemesanan_id',
                '=',
                'dps.id'
            )
            ->join(
                'pemesanans as pm',
                'dps.pemesanan_id',
                '=',
                'pm.id'
            )
            ->where('dpb.stok_id', $stokId)
            ->where('pm.status', 'approved_admin_gudang')
            ->select([
                'dpp.created_at as tanggal',
                DB::raw("'keluar' as tipe"),
                DB::raw("CONCAT('BAST-', p.no_surat) as no_surat"),
                'dpp.quantity',
                'dpp.harga',
                'dpp.subtotal'
            ]);
        $details = $stok->detailPenerimaanBarang()
            ->where('is_layak', true)
            ->whereHas('penerimaan', fn($q) => $q->whereIn('status', $validStatuses))
            ->get();
        $stokMasuk = $details->sum('quantity');
        $stokKeluar = $details
            ->flatMap(fn($d) => $d->detailPemesanans)
            ->sum('pivot.quantity');
        $totalStok = $stokMasuk - $stokKeluar;
        $mutasi = $masuk
            ->unionAll($keluar)
            ->orderBy('tanggal', 'desc')
            ->paginate(10);

        return [
            'id' => $stok->id,
            'name' => $stok->name,
            'category_name' => $stok->category->name,
            'satuan' => $stok->satuan->name ?? null,
            'minimum_stok' => $stok->minimum_stok,
            'total_stok' => $totalStok,
            'mutasi' => $mutasi
        ];
    }
    public function edit($id)
    {
        return Stok::where('id', $id)
            ->select(['name', 'minimum_stok'])
            ->firstOrFail();
    }
    public function update(array $data, $id)
    {
        $stok = Stok::findOrFail($id);
        $allowedData = collect($data)->only(['name', 'minimum_stok'])->toArray();
        $stok->update($allowedData);

        return $stok;
    }
    public function getAvailableStock(int $stokId): int
    {
        $stok = Stok::with([
            'detailPenerimaanBarang' => fn($q) =>
                $q->where('is_layak', true)
                    ->whereHas(
                        'penerimaan',
                        fn($p) =>
                        $p->whereIn('status', ['confirmed', 'signed', 'paid'])
                    )
        ])->findOrFail($stokId);

        $masuk = $stok->detailPenerimaanBarang->sum('quantity');

        $keluar = $stok->detailPenerimaanBarang
            ->flatMap(fn($d) => $d->detailPemesanans)
            ->sum('pivot.quantity');
        $total = ($masuk - $keluar) - $stok->minimum_stok;
        return $total;
    }
}
