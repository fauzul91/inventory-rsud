<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\V1\BastService;
use App\Services\V1\PenerimaanService;
use Exception;

class PenerimaanWorkflowController extends Controller
{
    private PenerimaanService $penerimaanService;
    private BastService $bastService;

    public function __construct(PenerimaanService $penerimaanService, BastService $bastService)
    {
        $this->penerimaanService = $penerimaanService;
        $this->bastService = $bastService;
    }
    public function confirmPenerimaan(string $id)
    {
        $result = $this->penerimaanService->confirmPenerimaan($id);
        if ($result['success'] === false) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $result['message'], null, 422);
        }
        $bast = $this->bastService->generateBast($id);

        return ResponseHelper::jsonResponse(true, 'Status penerimaan berhasil dikonfirmasi & BAST berhasil dibuat', ['penerimaan' => $result['data'], 'bast' => $bast], 200);
    }
    public function markDetailAsPaid($penerimaanId, $detailId)
    {
        $data = $this->penerimaanService->markDetailAsPaid($penerimaanId, $detailId);
        if (is_array($data) && isset($data['success']) && $data['success'] === false) {
            return ResponseHelper::jsonResponse(false, $data['message'], null, 404);
        }
        return ResponseHelper::jsonResponse(true, 'Barang berhasil dibayar', $data, 200);
    }
}
