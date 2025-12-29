<?php

namespace App\Services\V1;

use App\Models\Category;
use App\Models\Satuan;
use App\Repositories\V1\PenerimaanRepository;
use App\Models\Stok;
use Illuminate\Support\Facades\Log;

class DetailBarangService
{
    private PenerimaanRepository $repository;

    public function __construct(PenerimaanRepository $repository)
    {
        $this->repository = $repository;
    }

    public function syncDetailBarang($penerimaan, array $barangs)
    {
        $requestBarangIds = collect($barangs)
            ->pluck('id')
            ->filter()
            ->toArray();

        $penerimaan->detailBarang()
            ->whereNotIn('id', $requestBarangIds)
            ->delete();

        $existingBarang = $penerimaan->detailBarang()->get()->keyBy('id');

        foreach ($barangs as $barang) {
            try {
                $this->validateBarangData($barang);

                $stok = $this->findOrCreateStok($barang, $penerimaan->category_id);
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
            } catch (\Exception $e) {
                Log::error("Error sync barang: " . $e->getMessage(), [
                    'barang' => $barang,
                    'penerimaan_id' => $penerimaan->id
                ]);
                throw new \Exception("Gagal memproses barang: " . ($barang['name'] ?? 'Unknown') . ". Error: " . $e->getMessage());
            }
        }
    }

    private function validateBarangData(array $barang)
    {
        if (!isset($barang['quantity']) || $barang['quantity'] < 1) {
            throw new \Exception("Quantity harus diisi dan minimal 1");
        }

        if (empty($barang['stok_id']) && empty($barang['name'])) {
            throw new \Exception("Harus menyertakan stok_id atau name untuk barang");
        }

        if (empty($barang['stok_id']) && empty($barang['satuan_id']) && empty($barang['satuan_name'])) {
            throw new \Exception("Untuk barang baru, harus menyertakan satuan_id atau satuan_name");
        }
    }

    private function findOrCreateStok(array $barang, $categoryId)
    {
        if (!empty($barang['stok_id'])) {
            $stok = Stok::find($barang['stok_id']);
            if ($stok) {
                return $stok;
            }
            throw new \Exception("Stok dengan ID {$barang['stok_id']} tidak ditemukan");
        }

        if (!empty($barang['name'])) {
            $stok = Stok::whereRaw('LOWER(name) = ?', [strtolower(trim($barang['name']))])
                ->first();
            if ($stok) {
                return $stok;
            }
        }

        return $this->createNewStok($barang, $categoryId);
    }

    private function createNewStok(array $barang, $categoryId)
    {
        if (empty($barang['name'])) {
            throw new \Exception("Nama barang harus diisi untuk membuat stok baru");
        }

        $satuan = $this->findOrCreateSatuan($barang);

        return Stok::create([
            'name' => trim($barang['name']),
            'category_id' => $categoryId,
            'minimum_stok' => $barang['minimum_stok'] ?? 0,
            'price' => $barang['harga'] ?? $barang['price'] ?? 0,
            'satuan_id' => $satuan->id,
        ]);
    }

    private function findOrCreateSatuan(array $barang)
    {
        if (!empty($barang['satuan_id'])) {
            $satuan = Satuan::find($barang['satuan_id']);
            if ($satuan) {
                return $satuan;
            }
            throw new \Exception("Satuan dengan ID {$barang['satuan_id']} tidak ditemukan");
        }

        if (!empty($barang['satuan_name'])) {
            $rawName = trim($barang['satuan_name']);
            $lower = strtolower($rawName);

            $existing = Satuan::whereRaw('LOWER(name) = ?', [$lower])->first();

            if ($existing) {
                return $existing;
            }

            return Satuan::create([
                'name' => ucfirst($rawName)
            ]);
        }

        return Satuan::firstOrCreate(['name' => 'Lainnya']);
    }

    private function resolvePrice(array $barang, Stok $stok)
    {
        return $barang['harga'] ?? $barang['price'] ?? $stok->price;
    }

    public function createMultiple($penerimaanId, array $barangs, $categoryId)
    {
        foreach ($barangs as $barang) {
            $this->createSingle($penerimaanId, $barang, $categoryId);
        }
    }

    public function createSingle($penerimaanId, array $barang, $categoryId)
    {
        $this->validateBarangData($barang);

        $stok = $this->findOrCreateStok($barang, $categoryId);
        $harga = $this->resolvePrice($barang, $stok);

        return $this->repository->createDetailBarang([
            'penerimaan_id' => $penerimaanId,
            'stok_id' => $stok->id,
            'quantity' => $barang['quantity'],
            'harga' => $harga,
            'total_harga' => $harga * $barang['quantity'],
            'is_layak' => $barang['is_layak'] ?? null,
            'is_paid' => $barang['is_paid'] ?? null,
        ]);
    }
}