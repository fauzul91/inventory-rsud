<?php

namespace App\Services\V1;

use App\Repositories\V1\PenerimaanRepository;
use App\Models\Stok;

class DetailBarangService
{
    private PenerimaanRepository $repository;

    public function __construct(PenerimaanRepository $repository)
    {
        $this->repository = $repository;
    }

    public function createMultiple($penerimaanId, array $barangs)
    {
        foreach ($barangs as $barang) {
            $this->createSingle($penerimaanId, $barang);
        }
    }

    public function createSingle($penerimaanId, array $barang)
    {
        $stok = Stok::findOrFail($barang['stok_id']);
        $harga = $this->resolvePrice($barang, $stok);

        return $this->repository->createDetailBarang([
            'penerimaan_id' => $penerimaanId,
            'stok_id' => $stok->id,
            'quantity' => $barang['quantity'],
            'harga' => $harga,
            'total_harga' => $harga * $barang['quantity'],
            'is_layak' => null,
        ]);
    }

    public function syncDetailBarang($penerimaan, array $barangs)
    {
        $existingBarang = $penerimaan->detailBarang->keyBy('id');

        foreach ($barangs as $barang) {
            $stok = Stok::findOrFail($barang['stok_id']);
            $harga = $this->resolvePrice($barang, $stok);

            $barangData = [
                'stok_id' => $stok->id,
                'quantity' => $barang['quantity'],
                'harga' => $harga,
                'total_harga' => $harga * $barang['quantity'],
            ];

            if (isset($barang['is_layak'])) {
                $barangData['is_layak'] = $barang['is_layak'];
            }

            // Update existing atau create new
            if (!empty($barang['id']) && $existingBarang->has($barang['id'])) {
                $this->repository->updateDetailBarang(
                    $existingBarang[$barang['id']],
                    $barangData
                );
            } else {
                $this->repository->createDetailBarang([
                    'penerimaan_id' => $penerimaan->id,
                    ...$barangData
                ]);
            }
        }
    }

    private function resolvePrice(array $barang, Stok $stok)
    {
        return $barang['harga'] ?? $barang['price'] ?? $stok->price;
    }
}