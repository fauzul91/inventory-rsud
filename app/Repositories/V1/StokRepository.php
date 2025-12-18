<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\StokRepositoryInterface;
use App\Models\DetailPenerimaanBarang;
use App\Models\Monitoring;
use App\Models\Penerimaan;
use App\Models\Stok;
use App\Models\StokHistory;
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
    public function getAllStoks($filters)
    {
        $query = Stok::query()->with('satuan');

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        return $query;
    }
    public function getStokById($stokId)
    {
        $stok = Stok::with([
            'category:id,name',
            'satuan:id,name',
        ])->findOrFail($stokId);

        $masuk = DB::table('detail_penerimaan_barangs as dpb')
            ->join('penerimaans as p', 'dpb.penerimaan_id', '=', 'p.id')
            ->where('dpb.stok_id', $stokId)
            ->where('dpb.is_layak', true)
            ->whereIn('p.status', ['checked', 'confirmed', 'signed', 'paid'])
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
            ->select([
                'dpp.created_at as tanggal',
                DB::raw("'keluar' as tipe"),
                DB::raw("CONCAT('BAST-', p.no_surat) as no_surat"), // 🔥 tetap BAST asal
                'dpp.quantity',
                'dpp.harga',
                'dpp.subtotal'
            ]);

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
            'mutasi' => $mutasi
        ];
    }
    public function getPaidBastStock($filters)
    {
        $query = Penerimaan::with(['category', 'detailPegawai.pegawai', 'detailBarang'])
            ->where('status', 'paid');

        if (!empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        if (!empty($filters['search'])) {
            $query->where('no_surat', 'like', '%' . $filters['search'] . '%');
        }

        return $query;
    }
    public function getUnpaidBastStock($filters)
    {
        $query = Penerimaan::with(['category', 'detailPegawai.pegawai', 'detailBarang'])
            ->where('status', 'signed');

        if (!empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        if (!empty($filters['search'])) {
            $query->where('no_surat', 'like', '%' . $filters['search'] . '%');
        }

        return $query;
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
                        $p->whereIn('status', ['checked', 'confirmed', 'signed', 'paid'])
                    )
        ])->findOrFail($stokId);

        $masuk = $stok->detailPenerimaanBarang->sum('quantity');

        $keluar = $stok->detailPenerimaanBarang
            ->flatMap(fn($d) => $d->detailPemesanans)
            ->sum('pivot.quantity');

        return $masuk - $keluar;
    }
}
