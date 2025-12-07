<?php

namespace App\Services\V1;

use App\Repositories\V1\PemesananRepository;
use App\Repositories\V1\StokRepository;

class PemesananService
{
    private PemesananRepository $pemesananRepository;
    private StokRepository $stokRepository;
    public function __construct(
        StokRepository $stokRepository, PemesananRepository $pemesananRepository
    ) {
        $this->stokRepository = $stokRepository;
        $this->pemesananRepository = $pemesananRepository;
    }
    public function getAllStoks(array $filters)
    {
        $perPage = $filters['per_page'] ?? 10;

        $stoks = $this->stokRepository->getAllStoks($filters)
            ->with([
                'category:id,name',
                'satuan:id,name',
                'histories'
            ])
            ->paginate($perPage);

        $stoks->getCollection()->transform(function ($stok) {
            $totalStok = $stok->histories->sum('remaining_qty');

            return [
                'id' => $stok->id,
                'name' => $stok->name,
                'category_name' => $stok->category->name,
                'total_stok' => $totalStok,
                'satuan' => $stok->satuan->name ?? null,
            ];
        });

        return $stoks;
    }
    public function getAllPemesanan(array $filters)
    {
        return $this->pemesananRepository->getAllPemesanan($filters);
    }
    public function create(array $data)
    {
        return $this->pemesananRepository->createPemesanan($data);
    }
    public function findById($id)
    {
        return $this->pemesananRepository->getPemesananById($id);
    }
    public function updateDetailQuantity(int $pemesananId, int $detailId, int $amount)
    {
        return $this->pemesananRepository->updateDetailQuantity($pemesananId, $detailId, $amount);
    }
}