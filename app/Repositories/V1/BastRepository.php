<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\BastRepositoryInterface;
use App\Models\Bast;
use App\Models\Penerimaan;
use Illuminate\Support\Facades\Storage;

class BastRepository implements BastRepositoryInterface
{
    public function generateBast($penerimaanId)
    {
        $penerimaan = Penerimaan::with(['detailBarang', 'detailPegawai.pegawai'])->findOrFail($penerimaanId);
        $pdf = PDF::loadView('pdf.bast', [
            'penerimaan' => $penerimaan,
        ]);

        $no = $penerimaan->no_surat;
        $filename = "bast/generated/$no.pdf";

        Storage::put($filename, $pdf->output());

        return Bast::create([
            'penerimaan_id' => $penerimaanId,
            'filename' => $filename,
        ]);
    }
    public function downloadBast($penerimaanId)
    {
        $bast = Bast::where('penerimaan_id', $penerimaanId)->latest()->firstOrFail();
        $path = $bast->filename;

        if (!Storage::exists($path)) {
            abort(404, "File BAST tidak ditemukan");
        }

        return Storage::download($path);
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
