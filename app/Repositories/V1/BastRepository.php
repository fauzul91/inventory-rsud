<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\BastRepositoryInterface;
use App\Models\Bast;
use App\Models\Penerimaan;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;

class BastRepository implements BastRepositoryInterface
{
    public function generateBast($penerimaanId)
    {
        $penerimaan = Penerimaan::with(['detailBarang', 'detailPegawai.pegawai'])
            ->findOrFail($penerimaanId);

        $cleanNoSurat = str_replace(['/', '\\', ' '], '-', $penerimaan->no_surat);
        $filename = "bast/generated/{$cleanNoSurat}.pdf";

        Pdf::view('pdf.bast', [
            'penerimaan' => $penerimaan
        ])
            ->format('a4')
            ->disk('public')
            ->save($filename);

        $bast = Bast::create([
            'penerimaan_id' => $penerimaanId,
            'filename' => $filename,
        ]);

        $bast->url = asset('storage/' . $filename);
        return $bast;
    }

    public function downloadBast($bastId)
    {
        $bast = Bast::findOrFail($bastId);
        $filePath = storage_path('app/public/' . $bast->filename); // path lengkap ke file

        if (!file_exists($filePath)) {
            throw new \Exception('File tidak ditemukan');
        }

        $cleanFileName = basename($bast->filename);
        return response()->download($filePath, $cleanFileName);
    }


    public function uploadBast($penerimaanId, $file)
    {
        $path = $file->store("bast/signed");
        $bast = Bast::where('penerimaan_id', $penerimaanId)->latest()->firstOrFail();

        $bast->update([
            'uploaded_signed_file' => $path,
            'uploaded_at' => now(),
        ]);

        return $bast;
    }
    public function historyBast()
    {
        return Bast::orderBy('uploaded_at', 'desc')->get();
    }
}
