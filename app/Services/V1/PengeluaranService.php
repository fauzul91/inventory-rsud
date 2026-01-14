<?php

namespace App\Services\V1;
use App\Enum\V1\NotificationType;
use App\Models\DetailPemesanan;
use App\Models\DetailPenerimaanBarang;
use App\Models\Pemesanan;
use App\Models\Stok;
use App\Repositories\V1\PengeluaranRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PengeluaranService
{
    private $pengeluaranRepository;
    private $stokService;
    private $notifikasiService;
    public function __construct(
        PengeluaranRepository $pengeluaranRepository,
        StokService $stokService,
        NotifikasiService $notifikasiService
    ) {
        $this->pengeluaranRepository = $pengeluaranRepository;
        $this->stokService = $stokService;
        $this->$notifikasiService = $$notifikasiService;
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
                $stok = Stok::where('id', $detail->stok_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $totalStok = $this->stokService
                    ->calculateTotalStok($stok->id);

                $sisaSetelahKeluar = $totalStok - $quantityAdmin;

                if ($sisaSetelahKeluar < $stok->minimum_stok) {
                    throw new \DomainException(
                        'Pengeluaran melebihi batas minimum stok'
                    );
                }
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
            $this->notifikasiService->completeNotification(
                NotificationType::KONFIRMASI_PEMESANAN_ADMIN,
                $pemesananId
            );
            return $pemesanan->load('detailPemesanan.penerimaanBarangs');
        });
    }

    public function getAvailableBastByStok(int $stokId)
    {
        $stok = Stok::findOrFail($stokId);
        $totalStok = $this->stokService->calculateTotalStok($stokId);
        $availableForAllocation = max(
            0,
            $totalStok - $stok->minimum_stok
        );

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
                    'bast_id' => $item->penerimaan?->no_surat,
                    'tanggal_bast' => $item->penerimaan?->created_at?->format('d-m-Y'),
                    'quantity_total' => $item->quantity,
                    'quantity_used' => $usedQty,
                    'quantity_remaining' => $remaining,
                    'harga' => $item->harga,
                ];
            })
            ->filter()
            ->values();

        $data->setCollection($collection);

        return [
            'meta' => [
                'total_stok' => $totalStok,
                'minimum_stok' => (float) $stok->minimum_stok,
                'available_for_allocation' => $availableForAllocation,
            ],
            'batches' => $data,
        ];
    }
}