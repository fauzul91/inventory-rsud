<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\V1\PenerimaanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Class PenerimaanHistoryController
 * Menangani riwayat (history) transaksi penerimaan barang, baik riwayat umum
 * maupun riwayat verifikasi kelayakan barang.
 * * @package App\Http\Controllers\Api\V1
 */
class PenerimaanHistoryController extends Controller
{
    /**
     * @var PenerimaanService
     */
    private PenerimaanService $penerimaanService;

    /**
     * PenerimaanHistoryController constructor.
     * * @param PenerimaanService $penerimaanService
     */
    public function __construct(PenerimaanService $penerimaanService)
    {
        $this->penerimaanService = $penerimaanService;
    }

    /**
     * Mengambil daftar riwayat umum penerimaan barang yang telah melewati proses awal.
     * Status mencakup: checked, confirmed, signed, dan paid.
     * * @param Request $request
     * @return JsonResponse
     */
    public function history(Request $request): JsonResponse
    {
        $filters = $request->only(['per_page', 'search']);
        $statuses = ['checked', 'confirmed', 'signed', 'paid'];

        $data = $this->penerimaanService->getPenerimaanList($filters, $statuses);

        return ResponseHelper::jsonResponse(true, 'History penerimaan berhasil diambil', $data);
    }

    /**
     * Mengambil daftar riwayat khusus pemeriksaan/verifikasi kelayakan barang.
     * Status mencakup: confirmed, signed, dan paid.
     * * @param Request $request
     * @return JsonResponse
     */
    public function checkHistory(Request $request): JsonResponse
    {
        $filters = $request->only(['per_page', 'search']);
        $statuses = ['confirmed', 'signed', 'paid'];

        $data = $this->penerimaanService->getPenerimaanList($filters, $statuses, 'check');

        return ResponseHelper::jsonResponse(true, 'History penerimaan berhasil diambil', $data);
    }
}