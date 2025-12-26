<?php

namespace App\Services\V1;

use App\Models\DetailPemesanan;
use App\Models\Pemesanan;
use App\Repositories\V1\PemesananRepository;
use App\Repositories\V1\StokRepository;
use DB;
use Illuminate\Support\Facades\Auth;

class PemesananService
{
    private PemesananRepository $pemesananRepository;
    private StokRepository $stokRepository;
    private NotifikasiService $notifikasiService;
    private MonitoringService $monitoringService;
    public function __construct(
        StokRepository $stokRepository,
        PemesananRepository $pemesananRepository,
        NotifikasiService $notifikasiService,
        MonitoringService $monitoringService
    ) {
        $this->stokRepository = $stokRepository;
        $this->pemesananRepository = $pemesananRepository;
        $this->notifikasiService = $notifikasiService;
        $this->monitoringService = $monitoringService;
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

            $stokKeluar = $details->flatMap(fn($d) => $d->detailPemesanans)
                ->sum('pivot.quantity');

            $availableForUser = ($stokMasuk - $stokKeluar) - $stok->minimum_stok;

            return [
                'id' => $stok->id,
                'name' => $stok->name,
                'satuan' => $stok->satuan->name ?? null,
                'category_name' => $stok->category->name ?? null,
                'total_stok' => $availableForUser > 0 ? $availableForUser : 0,
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
        return DB::transaction(function () use ($data) {
            $pemesanan = $this->pemesananRepository->createPemesanan($data);

            $this->notifikasiService->pemesananDiajukan($pemesanan, Auth::user()->name ?? "Instalasi");
            $this->monitoringService->log("Membuat pemesanan baru: {$pemesanan->id}", Auth::id());

            return $pemesanan;
        });
    }
    public function findById($id)
    {
        return $this->pemesananRepository->getPemesananById($id);
    }
    public function updateQuantityPenanggungJawab(int $pemesananId, array $details)
    {
        $pemesanan = DB::transaction(function () use ($pemesananId, $details) {
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
        $this->notifikasiService->konfirmasiPemesananAdmin($pemesanan, Auth::user()->name ?? "Penanggung Jawab");
        $this->monitoringService->log("Konfirmasi kuantitas pemesanan: {$pemesanan->id}", Auth::id());
        return $pemesanan;
    }
}