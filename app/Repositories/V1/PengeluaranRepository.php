<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\PengeluaranRepositoryInterface;
use App\Models\DetailPenerimaanBarang;
use App\Models\DetailPenerimaanPemesanan;
use App\Models\Pemesanan;
use App\Models\DetailPemesanan;
use Illuminate\Support\Facades\DB;
use App\Interfaces\V1\PemesananRepositoryInterface;

class PengeluaranRepository implements PengeluaranRepositoryInterface
{
    public function getAllPengeluaran(array $filters)
    {
        $query = DB::table('detail_pemesanan_penerimaan as dpp')
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
            ->join(
                'stoks as s',
                'dpb.stok_id',
                '=',
                's.id'
            )
            ->join(
                'categories as c',
                's.category_id',
                '=',
                'c.id'
            )
            ->select([
                'dpp.id',
                'p.no_surat',
                'pm.ruangan as instalasi',
                'c.name as category_name',
                'dpp.quantity',
                'dpp.harga',
                'dpp.subtotal',
                'dpp.created_at as tanggal_pengeluaran'
            ])
            ->orderBy('dpp.created_at', 'desc');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('p.no_surat', 'like', "%{$search}%")
                    ->orWhere('pm.ruangan', 'like', "%{$search}%")
                    ->orWhere('c.name', 'like', "%{$search}%");
            });
        }

        return $query->paginate($filters['per_page'] ?? 10);
    }

    public function saveGudangQuantity(DetailPemesanan $detail, int $quantity)
    {
        $detail->update([
            'quantity_admin_gudang' => $quantity
        ]);
    }

    public function insertAllocation(array $data)
    {
        DetailPenerimaanPemesanan::create($data);
    }

    public function getUsedQuantityByPenerimaan(int $detailPenerimaanId): int
    {
        return DB::table('detail_pemesanan_penerimaan')
            ->where('detail_penerimaan_id', $detailPenerimaanId)
            ->sum('quantity');
    }
    public function getLayakByStok(int $stokId)
    {
        return DetailPenerimaanBarang::with([
            'penerimaan:id,created_at,no_surat',
            'detailPemesanans'
        ])
            ->where('stok_id', $stokId)
            ->where('is_layak', true)
            ->orderBy('created_at', 'asc')
            ->paginate(perPage: 5);
    }
}