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
        $query = Pemesanan::with(['user:id,name'])
            ->selectRaw("
                id,
                user_id,
                ruangan,
                DATE_FORMAT(tanggal_pemesanan, '%d-%m-%Y') as tanggal_pemesanan,
                status  
            ")
            ->orderBy('created_at', 'desc');

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('ruangan', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = $filters['per_page'] ?? 10;
        $data = $query->paginate($perPage);

        $data->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'user_name' => $item->user->name,
                'ruangan' => $item->ruangan,
                'tanggal_pemesanan' => $item->tanggal_pemesanan
                    ? $item->tanggal_pemesanan->format('d-m-Y')
                    : null,
                'status' => $item->status,
            ];
        });

        return $data;
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
            'penerimaan:id,created_at',
            'detailPemesanans'
        ])
            ->where('stok_id', $stokId)
            ->where('is_layak', true)
            ->orderBy('created_at', 'asc')
            ->paginate(10);
    }
}