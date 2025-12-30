<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\V1\PenerimaanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Class PenerimaanCheckController
 * Menangani proses verifikasi dan pengecekan kelayakan barang yang diterima dari vendor.
 * * @package App\Http\Controllers\Api\V1
 */
class PenerimaanCheckController extends Controller
{
    /**
     * @var PenerimaanService
     */
    private PenerimaanService $penerimaanService;

    /**
     * PenerimaanCheckController constructor.
     * * @param PenerimaanService $penerimaanService
     */
    public function __construct(PenerimaanService $penerimaanService)
    {
        $this->penerimaanService = $penerimaanService;
    }

    /**
     * Mengambil daftar penerimaan barang yang perlu dicek atau sudah dicek.
     * * @param Request $request
     * @return JsonResponse
     */
    public function getAllCheckedPenerimaan(Request $request): JsonResponse
    {
        $filters = $request->only(['per_page', 'search']);

        $data = $this->penerimaanService->getPenerimaanList($filters, ['pending', 'checked'], 'check');

        return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil diambil', $data);
    }

    /**
     * Memperbarui status kelayakan barang per item dalam satu dokumen penerimaan.
     * * @param Request $request
     * @param mixed $penerimaanId
     * @param mixed $detailId
     * @return JsonResponse
     */
    public function updateKelayakanBarang(Request $request, $penerimaanId, $detailId): JsonResponse
    {
        $validated = $request->validate([
            'is_layak' => ['required', 'boolean'],
        ]);

        $data = $this->penerimaanService->updateKelayakanBarang($penerimaanId, $detailId, $validated);

        return ResponseHelper::jsonResponse(true, 'Status kelayakan diperbarui', $data);
    }
}