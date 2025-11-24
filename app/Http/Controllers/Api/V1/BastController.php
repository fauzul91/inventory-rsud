<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Interfaces\V1\BastRepositoryInterface;
use App\Services\V1\BastService;
use Exception;
use Illuminate\Http\Request;

class BastController extends Controller
{
    private BastService $bastService;

    public function __construct(BastService $bastService)
    {
        $this->bastService = $bastService;
    }
    public function getUnsignedBast(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->query('per_page'),
                'sort_by' => $request->query('sort_by'),
            ];

            $data = $this->bastService->getUnsignedBast($filters);
            return ResponseHelper::jsonResponse(true, 'Data Unsigned BAST berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
    public function getSignedBast(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->query('per_page'),
                'sort_by' => $request->query('sort_by'),
            ];

            $data = $this->bastService->getSignedBast($filters);            
            return ResponseHelper::jsonResponse(true, 'Data Signed BAST berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
    /**
     * Generate BAST PDF
     */
    public function generate($penerimaanId)
    {
        try {
            $result = $this->bastService->generateBast($penerimaanId);
            return ResponseHelper::jsonResponse(true, 'Dokumen BAST berhasil dibuat', $result, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    public function downloadUnsignedBast($bastId)
    {
        try {
            return $this->bastService->downloadUnsignedBast($bastId);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
    public function downloadSignedBast($bastId)
    {
        try {
            return $this->bastService->downloadSignedBast($bastId);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Upload BAST setelah ditandatangani
     */
    public function upload(Request $request, $penerimaanId)
    {
        try {
            $request->validate([
                'uploaded_signed_file' => 'required|file|mimes:pdf|max:4096',
            ]);

            $file = $request->file('uploaded_signed_file');
            $result = $this->bastService->uploadSignedBast($penerimaanId, $file);

            return ResponseHelper::jsonResponse(true, 'BAST bertandatangan berhasil diupload', $result, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Ambil riwayat BAST milik user yang login
     */
    public function historyBast(Request $request)
    {
        try {
            $filters = $request->only(['sort_by', 'per_page']);
            $history = $this->bastService->history($filters);

            return ResponseHelper::jsonResponse(true,'Data riwayat BAST berhasil diambil',$history,200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false,'Terjadi kesalahan: ' . $e->getMessage(),null,500);
        }
    }
}
