<?php

namespace App\Repositories\V1;

use App\Models\Pemesanan;
use App\Models\DetailPemesanan;
use Illuminate\Support\Facades\DB;
use App\Interfaces\V1\PemesananRepositoryInterface;

class PemesananRepository implements PemesananRepositoryInterface
{
    private StokRepository $stokRepository;
    public function __construct(
        StokRepository $stokRepository,
    ) {
        $this->stokRepository = $stokRepository;
    }
    public function getAllPemesanan(array $filters, string $status)
    {
        $query = Pemesanan::with(['user:id,name'])
            ->selectRaw("
                id,
                user_id,
                ruangan,
                DATE_FORMAT(tanggal_pemesanan, '%d-%m-%Y') as tanggal_pemesanan,
                status  
            ")
            ->where('status', '=', $status)
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
    public function getAllStatusPemesananInstalasi(array $filters)
    {
        $query = Pemesanan::with(['user:id,name'])
            ->selectRaw("
                id,
                user_id,
                ruangan,
                DATE_FORMAT(tanggal_pemesanan, '%d-%m-%Y') as tanggal_pemesanan,
                status  
            ")
            ->where('status', '!=', 'pending')
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
            foreach ($data['items'] as $item) {
                $availableStock = $this->stokRepository
                    ->getAvailableStock($item['stok_id']);

                if ($availableStock < $item['quantity']) {
                    throw new \DomainException(
                        "Stok tidak mencukupi. Tersedia: {$availableStock}, diminta: {$item['quantity']}"
                    );
                }
            }

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
        ])->findOrFail($id);
        $detailItems = $pemesanan->detailPemesanan()
            ->with(['stok.satuan'])
            ->paginate(10);

        $detailItems->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'stok_id' => $item->stok_id,
                'stok_name' => $item->stok->name,
                'satuan_name' => ucfirst($item->stok->satuan->name),
                'quantity' => $item->quantity,
                'quantity_pj' => $item->quantity_pj,
                'quantity_admin_gudang' => $item->quantity_admin_gudang,
            ];
        });

        return [
            'id' => $pemesanan->id,
            'tanggal_pemesanan' => $pemesanan->tanggal_pemesanan
                ? $pemesanan->tanggal_pemesanan->format('d-m-Y')
                : null,
            'user_name' => $pemesanan->user->name,
            'ruangan' => $pemesanan->ruangan,
            'status' => $pemesanan->status,
            'detail_items' => $detailItems
        ];
    }

    public function updateQuantityPenanggungJawab(int $pemesananId, int $detailId, int $newQuantity)
    {
        $detail = DetailPemesanan::where('pemesanan_id', $pemesananId)
            ->findOrFail($detailId);

        if ($detail->pemesanan_id !== $pemesananId) {
            throw new \DomainException('Detail tidak sesuai dengan pemesanan.');
        }
        if ($newQuantity < 1) {
            throw new \DomainException('Quantity tidak boleh kurang dari 1.');
        }

        $detail->quantity_pj = $newQuantity ?? $detail->quantity;
        $detail->save();

        $detail->pemesanan->status = 'approved_pj';
        $detail->pemesanan->save();
        return $detail;
    }
}
