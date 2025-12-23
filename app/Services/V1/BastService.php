<?php

namespace App\Services\V1;

use App\Models\Bast;
use App\Models\Penerimaan;
use App\Repositories\V1\BastRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class BastService
{
    protected $bastRepository;
    protected $monitoringService;

    public function __construct(BastRepository $bastRepository, MonitoringService $monitoringService)
    {
        $this->bastRepository = $bastRepository;
        $this->monitoringService = $monitoringService;
    }

    public function getBastList(array $filters, string $type)
    {
        $statuses = match ($type) {
            'unsigned' => ['confirmed'],
            'signed' => ['signed', 'paid'],
            default => [],
        };

        $data = $this->bastRepository->getBastList($filters, $statuses);
        $transformed = $data->getCollection()->map(
            fn($item) => $this->transformBastItem($item, $type)
        );

        $data->setCollection($transformed);

        return $data;
    }
    private function transformBastItem($item, string $type): array
    {
        $pegawai = optional($item->detailPegawai->first()?->pegawai);

        return [
            'id' => $item->id,
            'no_surat' => $item->no_surat,
            'role_user' => $item->user?->roles->pluck('name')->first(),
            'category_name' => $item->category?->name,
            'pegawai_name' => $pegawai->name,
            'status' => $type === 'signed'
                ? 'Telah Ditandatangani'
                : 'Belum Ditandatangani',

            'bast' => $this->mapBastFile($item, $type),
        ];
    }
    private function mapBastFile($item, string $type): ?array
    {
        if (!$item->bast) {
            return null;
        }

        return match ($type) {
            'unsigned' => [
                'id' => $item->bast->id,
                'file_url' => asset('storage/' . $item->bast->filename),
                'download_endpoint' => route(
                    'bast.unsigned.download',
                    ['id' => $item->bast->id]
                ),
            ],

            'signed' => [
                'id' => $item->bast->id,
                'signed_file_url' => $item->bast->uploaded_signed_file
                    ? asset('storage/' . $item->bast->uploaded_signed_file)
                    : null,
                'download_endpoint' => route(
                    'bast.signed.download',
                    ['id' => $item->bast->id]
                ),
            ],

            default => null,
        };
    }

    public function generateBast($penerimaanId)
    {
        $penerimaan = $this->bastRepository->findPenerimaan($penerimaanId);

        $cleanNoSurat = str_replace(['/', '\\', ' '], '-', $penerimaan->no_surat);
        $filename = "bast/generated/{$cleanNoSurat}.pdf";

        $pdf = Pdf::loadView('pdf.bast', compact('penerimaan'))
            ->setPaper('legal', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true); // Untuk load external images

        Storage::disk('public')->put($filename, $pdf->output());

        $bast = $this->bastRepository->createBast($penerimaanId, $filename);
        $bast->url = asset('storage/' . $filename);

        return $bast;
    }

    public function downloadBastFile(Bast $bast, string $type = 'unsigned')
    {
        $fileField = match ($type) {
            'signed' => 'uploaded_signed_file',
            default => 'filename',
        };

        if (empty($bast->$fileField)) {
            throw new \Exception(
                ucfirst($type) . ' BAST belum tersedia'
            );
        }

        $filePath = storage_path('app/public/' . $bast->$fileField);

        if (!file_exists($filePath)) {
            throw new \Exception(
                ucfirst($type) . ' BAST tidak ditemukan'
            );
        }

        $this->monitoringService->log(
            'Download ' . strtoupper($type) . ' BAST',
            2
        );

        return response()->download(
            $filePath,
            basename($bast->$fileField)
        );
    }

    public function uploadSignedBast($penerimaanId, $file)
    {
        $path = $file->store('bast/signed', 'public');
        $bast = $this->bastRepository->findBastByPenerimaanId($penerimaanId);

        if (!$bast) {
            throw new \Exception("BAST untuk penerimaan ini tidak ditemukan.");
        }

        $this->bastRepository->updateSignedBast($bast, $path);
        Penerimaan::where('id', $penerimaanId)->update([
            'status' => 'signed'
        ]);

        $this->monitoringService->log("Upload SIGNED BAST", 2);

        return $bast;
    }
    public function history($filters)
    {
        $paginated = $this->bastRepository->getHistory($filters);

        $paginated->getCollection()->transform(function ($bast) {
            return [
                'id' => $bast->id,
                'filename' => $bast->filename,
                'signed_file' => $bast->uploaded_signed_file ? asset('storage/' . $bast->uploaded_signed_file) : null,
                'uploaded_at' => $bast->uploaded_at,
                'penerimaan_no_surat' => $bast->penerimaan->no_surat,
            ];
        });

        return $paginated;
    }
}
