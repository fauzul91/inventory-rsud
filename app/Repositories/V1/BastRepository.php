<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\BastRepositoryInterface;
use App\Models\Bast;
use App\Models\Monitoring;
use App\Models\Penerimaan;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Facades\Pdf;

class BastRepository implements BastRepositoryInterface
{
    public function getUnsignedBast(array $filters)
    {
        $query = Penerimaan::with(['category', 'detailPegawai.pegawai', 'detailBarang', 'bast'])->where('status', 'confirmed');

        if (!empty($filters['sort_by'])) {
            if ($filters['sort_by'] === 'latest') {
                $query->orderBy('created_at', 'desc');
            } elseif ($filters['sort_by'] === 'oldest') {
                $query->orderBy('created_at', 'asc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $filters['per_page'] ?? 10;
        $penerimaan = $query->paginate($perPage);

        return $penerimaan;
    }
    public function getSignedBast(array $filters)
    {
        $query = Penerimaan::with(['category', 'detailPegawai.pegawai', 'detailBarang'])->where('status', 'signed');

        if (!empty($filters['sort_by'])) {
            if ($filters['sort_by'] === 'latest') {
                $query->orderBy('created_at', 'desc');
            } elseif ($filters['sort_by'] === 'oldest') {
                $query->orderBy('created_at', 'asc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $filters['per_page'] ?? 10;
        $penerimaan = $query->paginate($perPage);

        return $penerimaan;
    }
    public function generateBast($penerimaanId)
    {
        $penerimaan = Penerimaan::with(['detailBarang', 'detailPegawai.pegawai'])
            ->findOrFail($penerimaanId);

        $cleanNoSurat = str_replace(['/', '\\', ' '], '-', $penerimaan->no_surat);
        $filename = "bast/generated/{$cleanNoSurat}.pdf";

        Pdf::view('pdf.bast', [
            'penerimaan' => $penerimaan
        ])
            ->format(Format::Legal)
            ->margins(40, 20, 40, 20)
            ->disk('public')
            ->save($filename);
        $bast = Bast::create([
            'penerimaan_id' => $penerimaanId,
            'filename' => $filename,
        ]);

        $bast->url = asset('storage/' . $filename);
        return $bast;
    }

    public function downloadUnsignedBast($bastId)
    {
        $bast = Bast::findOrFail($bastId);
        $filePath = storage_path('app/public/' . $bast->filename); // path lengkap ke file

        if (!file_exists($filePath)) {
            throw new \Exception('File unsigned tidak ditemukan');
        }

        $userId = 2;

        Monitoring::create([
            'user_id' => $userId,
            'time' => now()->format('H:i:s'),
            'date' => now()->format('Y-m-d'),
            'activity' => "Download Unsigned BAST",
        ]);

        $cleanFileName = basename($bast->filename);
        return response()->download($filePath, $cleanFileName);
    }
    public function downloadSignedBast($bastId)
    {
        $bast = Bast::findOrFail($bastId);

        if (!$bast->uploaded_signed_file) {
            throw new \Exception('File signed belum tersedia');
        }

        $filePath = storage_path('app/public/' . $bast->uploaded_signed_file);

        if (!file_exists($filePath)) {
            throw new \Exception('File signed tidak ditemukan');
        }

        $userId = 2;
        Monitoring::create([
            'user_id' => $userId,
            'time' => now()->format('H:i:s'),
            'date' => now()->format('Y-m-d'),
            'activity' => "Download SIGNED BAST",
        ]);

        return response()->download($filePath, basename($bast->uploaded_signed_file));
    }

    public function uploadBast($penerimaanId, $file)
    {
        $path = $file->store('bast/signed', 'public');
        $relativePath = $path;

        $bast = Bast::where('penerimaan_id', $penerimaanId)->latest()->firstOrFail();
        $bast->update([
            'uploaded_signed_file' => $relativePath,
            'uploaded_at' => now(),
        ]);

        Penerimaan::where('id', $penerimaanId)->update([
            'status' => 'signed'
        ]);

        $userId = 2;

        Monitoring::create([
            'user_id' => $userId,
            'time' => now()->format('H:i:s'),
            'date' => now()->format('Y-m-d'),
            'activity' => "Mengunggah BAST",
        ]);

        return $bast;
    }

    public function historyBast(array $filters)
    {
        $query = Bast::with(['penerimaan.category', 'penerimaan.detailPegawai.pegawai', 'penerimaan.detailBarang'])
            ->orderBy('uploaded_at', 'desc');

        if (!empty($filters['sort_by'])) {
            if ($filters['sort_by'] === 'latest') {
                $query->orderBy('uploaded_at', 'desc');
            } elseif ($filters['sort_by'] === 'oldest') {
                $query->orderBy('uploaded_at', 'asc');
            }
        }

        $perPage = $filters['per_page'] ?? 10;
        $paginated = $query->paginate($perPage);

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
