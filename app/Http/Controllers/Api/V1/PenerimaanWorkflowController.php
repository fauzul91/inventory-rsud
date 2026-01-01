<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\V1\BastService;
use App\Services\V1\PenerimaanService;
use Illuminate\Http\JsonResponse;

/**
 * Class PenerimaanWorkflowController
 * Mengelola alur kerja kritis pasca-penerimaan, termasuk konfirmasi final, 
 * pembuatan dokumen BAST otomatis, dan pencatatan status pembayaran item.
 * * @package App\Http\Controllers\Api\V1
 */
class PenerimaanWorkflowController extends Controller
{
    /**
     * @var PenerimaanService
     */
    private PenerimaanService $penerimaanService;

    /**
     * @var BastService
     */
    private BastService $bastService;

    /**
     * PenerimaanWorkflowController constructor.
     * * @param PenerimaanService $penerimaanService
     * @param BastService $bastService
     */
    public function __construct(PenerimaanService $penerimaanService, BastService $bastService)
    {
        $this->penerimaanService = $penerimaanService;
        $this->bastService = $bastService;
    }

    /**
     * Mengonfirmasi penerimaan barang dan menghasilkan dokumen BAST secara otomatis.
     * Menggunakan pendekatan Exception-based error handling untuk menjaga Cyclomatic Complexity tetap rendah.
     */
    public function confirmPenerimaan(string $id): JsonResponse
    {
        $penerimaan = $this->penerimaanService->confirmPenerimaan($id);
        $bast = $this->bastService->generateBast($id);

        return ResponseHelper::jsonResponse(true, 'Konfirmasi sukses & BAST dibuat', [
            'penerimaan' => $penerimaan,
            'bast' => $bast
        ]);
    }

    /**
     * Menandai detail item barang tertentu dalam penerimaan sebagai sudah dibayar (paid).
     * @return JsonResponse
     */
    public function markDetailAsPaid($penerimaanId, $detailId): JsonResponse
    {
        $data = $this->penerimaanService->markDetailAsPaid($penerimaanId, $detailId);

        return ResponseHelper::jsonResponse(true, 'Barang berhasil dibayar', $data);
    }
}