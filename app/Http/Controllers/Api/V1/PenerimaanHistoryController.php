<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\V1\PenerimaanService;
use Exception;
use Illuminate\Http\Request;

class PenerimaanHistoryController extends Controller
{
    private PenerimaanService $penerimaanService;

    public function __construct(PenerimaanService $penerimaanService)
    {
        $this->penerimaanService = $penerimaanService;
    }
    public function history(Request $request)
    {
        $filters = $request->only(['per_page', 'search']);
        $data = $this->penerimaanService->getPenerimaanList($filters, ['checked', 'confirmed', 'signed', 'paid']);
        return ResponseHelper::jsonResponse(true, 'History penerimaan berhasil diambil', $data, 200);
    }
    public function checkHistory(Request $request)
    {
        $filters = $request->only(['per_page', 'search']);
        $data = $this->penerimaanService->getPenerimaanList($filters, ['confirmed', 'signed', 'paid'], 'check');
        return ResponseHelper::jsonResponse(true, 'History penerimaan berhasil diambil', $data, 200);
    }
}