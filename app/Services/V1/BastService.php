<?php

namespace App\Services\V1;

use App\Models\Penerimaan;
use App\Repositories\V1\BastRepository;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\Enums\Format;

class BastService
{
    protected $bastRepository;
    protected $monitoringService;

    public function __construct(BastRepository $bastRepository, MonitoringService $monitoringService)
    {
        $this->bastRepository = $bastRepository;
        $this->monitoringService = $monitoringService;
    }

    public function getUnsignedBast($filters)
    {
        $data = $this->bastRepository->getUnsignedBast($filters);
        $transformed = $data->getCollection()->map(function ($item) {
            return [
                'id' => $item->id,
                'no_surat' => $item->no_surat,
                'role_user' => $item->user->roles->pluck('name')->first() ?? null,
                'category_name' => $item->category->name ?? null,
                'pegawai_name' => optional($item->detailPegawai->first()->pegawai)->name ?? null,
                'status' => 'Belum Ditandatangani',
                'bast' => $item->status === 'confirmed' && $item->bast ? [
                    'id' => $item->bast->id,
                    'file_url' => asset('storage/' . $item->bast->filename),
                    'download_endpoint' => route('bast.unsigned.download', ['id' => $item->bast->id]),
                ] : null,
            ];
        });

        $data->setCollection($transformed);

        return $data;
    }

    public function getSignedBast($filters)
    {
        $data = $this->bastRepository->getSignedBast($filters);

        $transformed = $data->getCollection()->map(function ($item) {
            return [
                'id' => $item->id,
                'no_surat' => $item->no_surat,
                'role_user' => $item->user->roles->pluck('name')->first() ?? null,
                'category_name' => $item->category->name ?? null,
                'pegawai_name' => optional($item->detailPegawai->first()->pegawai)->name ?? null,
                'status' => $item->status === 'signed' || $item->status === 'paid' ? 'Telah Ditandatangani' : 'Belum Ditandatangani',
                'bast' => $item->bast ? [
                    'id' => $item->bast->id,
                    'signed_file_url' => $item->bast->uploaded_signed_file
                        ? asset('storage/' . $item->bast->uploaded_signed_file)
                        : null,
                    'download_endpoint' => route('bast.signed.download', ['id' => $item->bast->id]),
                ] : null,
            ];
        });

        $data->setCollection($transformed);

        return $data;
    }

    public function generateBast($penerimaanId)
    {
        $penerimaan = $this->bastRepository->findPenerimaan($penerimaanId);

        $cleanNoSurat = str_replace(['/', '\\', ' '], '-', $penerimaan->no_surat);
        $filename = "bast/generated/{$cleanNoSurat}.pdf";

        Pdf::view('pdf.bast', compact('penerimaan'))
            ->format(Format::Legal)
            ->margins(40, 20, 40, 20)
            ->disk('public')
            ->save($filename);

        $bast = $this->bastRepository->createBast($penerimaanId, $filename);
        $bast->url = asset('storage/' . $filename);

        return $bast;
    }

    public function downloadUnsignedBast($bastId)
    {
        $bast = $this->bastRepository->findBast($bastId);
        $filePath = storage_path('app/public/' . $bast->filename);

        if (!file_exists($filePath)) {
            throw new \Exception('Unsigned BAST tidak ditemukan.');
        }

        $this->monitoringService->log("Download SIGNED BAST", 2);

        return response()->download($filePath, basename($bast->filename));
    }

    public function downloadSignedBast($bastId)
    {
        $bast = $this->bastRepository->findBast($bastId);

        if (!$bast->uploaded_signed_file) {
            throw new \Exception('Signed file belum tersedia');
        }

        $filePath = storage_path('app/public/' . $bast->uploaded_signed_file);

        if (!file_exists($filePath)) {
            throw new \Exception('Signed file tidak ditemukan');
        }

        $this->monitoringService->log("Download SIGNED BAST", 2);

        return response()->download($filePath, basename($bast->uploaded_signed_file));
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
