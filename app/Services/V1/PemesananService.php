<?php

namespace App\Services\V1;

use App\Models\DetailPemesanan;
use App\Models\Pemesanan;
use App\Repositories\V1\PemesananRepository;
use App\Repositories\V1\StokRepository;
use DB;

class PemesananService
{
    private PemesananRepository $pemesananRepository;
    private StokRepository $stokRepository;
    public function __construct(
        StokRepository $stokRepository,
        PemesananRepository $pemesananRepository
    ) {
        $this->stokRepository = $stokRepository;
        $this->pemesananRepository = $pemesananRepository;
    }
    public function getAllStoks(array $filters)
    {
        $perPage = $filters['per_page'] ?? 10;
        $query = $this->stokRepository->getAllStoks($filters);
        $query->with([
            'category:id,name',
            'satuan:id,name',
            'detailPenerimaanBarang' => fn($q) =>
                $q->where('is_layak', true)
                    ->whereHas(
                        'penerimaan',
                        fn($p) =>
                        $p->whereIn('status', ['checked', 'confirmed', 'signed', 'paid'])
                    )
        ])->orderBy('name');

        $stoks = $query->paginate($perPage);
        $stoks->getCollection()->transform(function ($stok) {
            $detailPenerimaan = $stok->detailPenerimaanBarang;
            return [
                'id' => $stok->id,
                'name' => $stok->name,
                'category_name' => $stok->category->name,
                'total_stok' => $detailPenerimaan->sum('quantity'),
                'satuan' => $stok->satuan->name ?? null,
            ];
        });

        return $stoks;
    }
    public function getAllPemesanan(array $filters, string $status)
    {
        return $this->pemesananRepository->getAllPemesanan($filters, $status);
    }
    public function getAllStatusPemesananInstalasi(array $filters)
    {
        return $this->pemesananRepository->getAllStatusPemesananInstalasi($filters);
    }
    public function create(array $data)
    {
        return $this->pemesananRepository->createPemesanan($data);
    }
    public function findById($id)
    {
        return $this->pemesananRepository->getPemesananById($id);
    }
    public function updateQuantityPenanggungJawab(int $pemesananId, array $details)
    {
        $data = DB::transaction(function () use ($pemesananId, $details) {
            foreach ($details as $item) {
                $this->pemesananRepository
                    ->updateQuantityPenanggungJawab(
                        $pemesananId,
                        $item['detail_id'],
                        $item['quantity_pj']
                    );
            }

            DetailPemesanan::where('pemesanan_id', $pemesananId)
                ->whereNull('quantity_pj')
                ->update([
                    'quantity_pj' => DB::raw('quantity')
                ]);

            return Pemesanan::with('detailPemesanan')->find($pemesananId);
        });

        return $data;
    }
}