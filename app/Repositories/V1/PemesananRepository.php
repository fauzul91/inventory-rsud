<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\PemesananRepositoryInterface;
use App\Models\DetailPemesanan;
use App\Models\Pemesanan;
use Illuminate\Support\Facades\DB;

class PemesananRepository implements PemesananRepositoryInterface
{
    public function getAllPemesanan(array $filters)
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
    public function createPemesanan(array $data)
    {
        return DB::transaction(function () use ($data) {

            $pemesanan = Pemesanan::create([
                'user_id' => auth()->id() ?? 6,
                'ruangan' => $data['ruangan'],
                'nama_pj_instalasi' => $data['nama_pj_instalasi'] ?? null,
                'tanggal_pemesanan' => now(),
                'status' => 'pending'
            ]);

            foreach ($data['items'] as $item) {
                DetailPemesanan::create([
                    'pemesanan_id' => $pemesanan->id,
                    'stok_id' => $item['stok_id'],
                    'quantity' => $item['quantity']
                ]);
            }

            return $pemesanan->load(['detailPemesanan.stok']);
        });
    }
    public function getPemesananById($id)
    {
        $pemesanan = Pemesanan::with([
            'user:id,name',
            'detailPemesanan.stok'
        ])->findOrFail($id);

        return [
            'id' => $pemesanan->id,
            'tanggal_pemesanan' => $pemesanan->tanggal_pemesanan
                ? $pemesanan->tanggal_pemesanan->format('d-m-Y')
                : null,
            'user_name' => $pemesanan->user->name,
            'ruangan' => $pemesanan->ruangan,
            'status' => $pemesanan->status,

            'detail_items' => $pemesanan->detailPemesanan->map(function ($item) {
                return [
                    'id' => $item->id,
                    'stok_id' => $item->stok_id,
                    'stok_name' => $item->stok->name,
                    'satuan_name' => ucfirst($item->stok->satuan->name),
                    'quantity' => $item->quantity,
                ];
            }),
        ];
    }

    public function updateDetailQuantity($pemesananId, $detailId, $amount)
    {
        $detail = DetailPemesanan::where('pemesanan_id', $pemesananId)
            ->findOrFail($detailId);

        $newQty = $detail->quantity + $amount;

        if ($newQty < 1) {
            throw new \Exception("Quantity tidak boleh kurang dari 1.");
        }

        $detail->update(['quantity' => $newQty]);
        return $detail;
    }
}