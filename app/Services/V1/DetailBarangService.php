<?php

namespace App\Services\V1;

use App\Models\Category;
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
        $stok = $this->findOrCreateStok($barang);
        $harga = $this->resolvePrice($barang, $stok);

        return $this->repository->createDetailBarang([
            'penerimaan_id' => $penerimaanId,
            'stok_id' => $stok->id,
            'quantity' => $barang['quantity'],
            'quantity_layak' => $barang['quantity_layak'] ?? null, 
            'quantity_tidak_layak' => $barang['quantity_tidak_layak'] ?? null,
            'harga' => $harga,
            'total_harga' => $harga * $barang['quantity'],
            'is_paid' => $barang['is_paid'] ?? null,
        ]);
    }
    private function findOrCreateStok(array $barang)
    {
        if (!empty($barang['stok_id'])) {
            $stok = Stok::find($barang['stok_id']);
            if ($stok) return $stok;
        }
        if (!empty($barang['name'])) {
            $stok = Stok::whereRaw('LOWER(name) = ?', [strtolower($barang['name'])])->first();
            if ($stok) return $stok;
        }
        $category = $this->findOrCreateCategory($barang);
        return Stok::create([
            'name' => $barang['name'] ?? 'Barang Tanpa Nama',
            'category_id' => $category->id,
            'minimum_stok' => $barang['minimum_stok'] ?? 0,
            'price' => $barang['harga'] ?? $barang['price'] ?? 0,
            'satuan_id' => $barang['satuan_id'] ?? null,
        ]);
    }
    private function findOrCreateCategory(array $barang)
    {
        if (!empty($barang['category_id'])) {
            $category = Category::find($barang['category_id']);
            if ($category) return $category;
        }
        if (!empty($barang['category_name'])) {
            return Category::firstOrCreate([
                'name' => $barang['category_name']
            ]);
        }
        return Category::firstOrCreate(['name' => 'Lainnya']);
    }
    public function syncDetailBarang($penerimaan, array $barangs)
    {
        $existingBarang = $penerimaan->detailBarang->keyBy('id');

        foreach ($barangs as $barang) {
            $stok = $this->findOrCreateStok($barang);
            $harga = $this->resolvePrice($barang, $stok);

            $barangData = [
                'stok_id' => $stok->id,
                'quantity' => $barang['quantity'],
                'quantity_layak' => $barang['quantity_layak'] ?? null,
                'quantity_tidak_layak' => $barang['quantity_tidak_layak'] ?? null,
                'harga' => $harga,
                'total_harga' => $harga * $barang['quantity'],
            ];

            if (isset($barang['is_paid'])) {
                $barangData['is_paid'] = $barang['is_paid'];
            }

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