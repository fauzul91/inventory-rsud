<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\V1\BastService;
use App\Services\V1\PenerimaanService;
use Illuminate\Http\JsonResponse;

class PenerimaanWorkflowController extends Controller
{
    private PenerimaanService $penerimaanService;
    private BastService $bastService;

    public function __construct(PenerimaanService $penerimaanService, BastService $bastService)
    {
        $this->penerimaanService = $penerimaanService;
        $this->bastService = $bastService;
    }
    public function confirmPenerimaan(string $id): JsonResponse
    {
        $penerimaan = $this->penerimaanService->confirmPenerimaan($id);
        $bast = $this->bastService->generateBast($id);

        return ResponseHelper::jsonResponse(true, 'Konfirmasi sukses & BAST dibuat', [
            'penerimaan' => $penerimaan,
            'bast' => $bast
        ]);
    }

    public function markDetailAsPaid($penerimaanId, $detailId): JsonResponse
    {
        $data = $this->penerimaanService->markDetailAsPaid($penerimaanId, $detailId);

        return ResponseHelper::jsonResponse(true, 'Barang berhasil dibayar', $data);
    }
}