<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Interfaces\V1\BastRepositoryInterface;
use App\Repositories\V1\BastRepository;
use App\Services\V1\BastService;
use App\Services\V1\PenerimaanService;
use Exception;
use Illuminate\Http\Request;

class BastController extends Controller
{
    private PenerimaanService $penerimaanService;
    private BastRepository $bastRepository;
    private BastService $bastService;

    public function __construct(BastService $bastService, BastRepository $bastRepository, PenerimaanService $penerimaanService)
    {
        $this->bastRepository = $bastRepository;
        $this->bastService = $bastService;
        $this->penerimaanService = $penerimaanService;
    }
    public function getUnsignedBast(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->query('per_page'),
                'sort_by' => $request->query('sort_by'),
                'search' => $request->query('search'),
            ];

            $data = $this->bastService->getBastList($filters, 'unsigned');
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
                'search' => $request->query('search'),
            ];

            $data = $this->bastService->getBastList($filters, 'signed');
            return ResponseHelper::jsonResponse(true, 'Data Signed BAST berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
    public function getAllPaymentBast(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->query('per_page'),
                'category' => $request->query('category'),
                'search' => $request->query('search'),
            ];

            $status = $request->query('status');
            $statuses = match ($status) {
                'unpaid' => ['signed'],
                'paid' => ['paid'],
                default => ['signed', 'paid'],
            };

            $data = $this->penerimaanService->getPenerimaanList($filters, $statuses, 'paid');

            return ResponseHelper::jsonResponse(true, 'Data bast berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }

    public function downloadUnsignedBast($bastId)
    {
        try {
            $bast = $this->bastRepository->findBast($bastId);
            return $this->bastService->downloadBastFile($bast, 'unsigned');
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
    public function downloadSignedBast($bastId)
    {
        try {
            $bast = $this->bastRepository->findBast($bastId);
            return $this->bastService->downloadBastFile($bast, 'signed');
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
}
