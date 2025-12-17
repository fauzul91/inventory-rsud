<?php

namespace App\Services\V1;
use App\Models\DetailPemesanan;
use App\Models\DetailPenerimaanBarang;
use App\Models\Pemesanan;
use App\Repositories\V1\PengeluaranRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PengeluaranService
{
    private PengeluaranRepository $pengeluaranRepository;
    public function __construct(
        PengeluaranRepository $pengeluaranRepository
    ) {
        $this->pengeluaranRepository = $pengeluaranRepository;
    }
    public function getAllPengeluaran($filters)
    {
        return $this->pengeluaranRepository->getAllPengeluaran($filters);
    }
    public function processGudangFulfillmentByPemesanan(
        int $pemesananId,
        array $detailsPayload
    ) {
        return DB::transaction(function () use ($pemesananId, $detailsPayload) {

            $pemesanan = Pemesanan::with('detailPemesanan')
                ->findOrFail($pemesananId);

            foreach ($detailsPayload as $item) {

                $detail = DetailPemesanan::where('pemesanan_id', $pemesananId)
                    ->findOrFail($item['detail_id']);

                $quantityAdmin = $item['quantity_admin'];
                $allocations = $item['allocations'];

                if ($quantityAdmin < 1) {
                    throw new \DomainException('Quantity tidak valid');
                }

                $totalAlloc = collect($allocations)->sum('quantity');
                if ($totalAlloc !== $quantityAdmin) {
                    throw new \DomainException(
                        'Total alokasi BAST harus sama dengan quantity admin gudang'
                    );
                }

                // simpan quantity admin gudang
                $this->pengeluaranRepository
                    ->saveGudangQuantity($detail, $quantityAdmin);

                foreach ($allocations as $alloc) {

                    $penerimaan = DetailPenerimaanBarang::findOrFail(
                        $alloc['detail_penerimaan_id']
                    );

                    $usedQty = $this->pengeluaranRepository
                        ->getUsedQuantityByPenerimaan($penerimaan->id);

                    $remaining = $penerimaan->quantity - $usedQty;

                    if ($alloc['quantity'] > $remaining) {
                        throw new \DomainException(
                            'Stok BAST tidak mencukupi'
                        );
                    }

                    $this->pengeluaranRepository->insertAllocation([
                        'detail_pemesanan_id' => $detail->id,
                        'detail_penerimaan_id' => $penerimaan->id,
                        'quantity' => $alloc['quantity'],
                        'harga' => $penerimaan->harga,
                        'subtotal' => $alloc['quantity'] * $penerimaan->harga,
                    ]);
                }
            }

            $pemesanan->update([
                'status' => 'approved_admin_gudang'
            ]);

            return $pemesanan->load('detailPemesanan.penerimaanBarangs');
        });
    }

    public function getAvailableBastByStok(int $stokId)
    {
        $data = $this->pengeluaranRepository
            ->getLayakByStok($stokId);
        $collection = $data->getCollection()
            ->map(function ($item) {
                $usedQty = $item->detailPemesanans
                    ->sum('pivot.quantity');

                $remaining = $item->quantity - $usedQty;
                if ($remaining <= 0) {
                    return null;
                }
                return [
                    'detail_penerimaan_id' => $item->id,
                    'bast_id' => $item->penerimaan_id,
                    'tanggal_bast' => Carbon::parse($item->penerimaan->created_at)->format('d-m-Y'),
                    'quantity_total' => $item->quantity,
                    'quantity_used' => $usedQty,
                    'quantity_remaining' => $remaining,
                    'harga' => $item->harga,
                ];
            })
            ->filter()
            ->values();

        $data->setCollection($collection);
        return $data;
    }
}