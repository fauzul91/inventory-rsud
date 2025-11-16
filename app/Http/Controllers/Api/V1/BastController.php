<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Interfaces\V1\BastRepositoryInterface;
use App\Repositories\V1\BastRepository;
use Exception;
use Illuminate\Http\Request;

class BastController extends Controller
{
    private BastRepository $bastRepository;

    public function __construct(BastRepositoryInterface $bastRepository)
    {
        $this->bastRepository = $bastRepository;
    }

    /**
     * Generate BAST PDF
     */
    public function generate($penerimaanId)
    {
        try {
            $result = $this->bastRepository->generateBast( $penerimaanId);
            return ResponseHelper::jsonResponse(true,'Dokumen BAST berhasil dibuat',$result,200);            
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    public function download($penerimaanId)
    {
        try {
            return $this->bastRepository->downloadBast($penerimaanId);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: '.$e->getMessage(), null, 500);
        }
    }

    /**
     * Upload BAST setelah ditandatangani
     */
    public function upload(Request $request, $penerimaanId)
    {
        try {
            $request->validate([
                'signed_file' => 'required|file|mimes:pdf|max:4096',
            ]);

            $file = $request->file('signed_file');
            $result = $this->bastRepository->uploadBast($penerimaanId, $file);

            return ResponseHelper::jsonResponse(true,'BAST bertandatangan berhasil diupload',$result,200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Ambil riwayat BAST milik user yang login
     */
    public function history(Request $request)
    {
        try {
            $history = $this->bastRepository->historyBast();
            return ResponseHelper::jsonResponse(true,'Data riwayat BAST berhasil diambil',$history,200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
}
