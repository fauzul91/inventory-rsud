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
        $stoks = $this->stokRepository
            ->getAllStoks($filters)
            ->paginate($perPage);

        $stoks->getCollection()->transform(function ($stok) {
            $details = $stok->detailPenerimaanBarang;
            $stokMasuk = $details->sum('quantity');
            $stokKeluar = $details
                ->flatMap(fn($d) => $d->detailPemesanans)
                ->sum('pivot.quantity');
            $totalStok = $stokMasuk - $stokKeluar;
            return [
                'id' => $stok->id,
                'name' => $stok->name,
                'satuan' => $stok->satuan->name ?? null,
                'category_name' => $stok->category->name,
                'total_stok' => $totalStok,
            ];
        });

        return $stoks;
    }
    public function getAllPemesanan(array $filters, array $statuses, string $context = 'default')
    {
        $data = $this->pemesananRepository->getAllPemesanan($filters, $statuses);

        $data->getCollection()->transform(function ($item) use ($context) {
            return [
                'id' => $item->id,
                'user_name' => $item->user->name,
                'ruangan' => $item->ruangan,
                'tanggal_pemesanan' => $item->tanggal_pemesanan
                    ? $item->tanggal_pemesanan->format('d-m-Y')
                    : null,
                'status' => $this->mapPemesananStatus($item->status, $context),
                'status_code' => $item->status,
            ];
        });

        return $data;
    }
    private function mapPemesananStatus(string $status, string $context = 'default')
    {
        return match ($context) {
            'pj' => match ($status) {
                    'pending' => 'Menunggu Persetujuan',
                    'approved_pj',
                    'approved_admin_gudang' => 'Disetujui PJ Ruangan',
                    default => '-',
                },

            default => match ($status) {
                    'pending' => 'Menunggu Persetujuan',
                    'approved_pj' => 'Disetujui PJ Ruangan',
                    'approved_admin_gudang' => 'Disetujui Admin Gudang',
                    default => '-',
                },
        };
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