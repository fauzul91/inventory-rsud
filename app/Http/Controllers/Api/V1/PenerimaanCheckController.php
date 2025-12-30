<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\V1\PenerimaanService;
use Exception;
use Illuminate\Http\Request;

class PenerimaanCheckController extends Controller
{
    private PenerimaanService $penerimaanService;

    public function __construct(PenerimaanService $penerimaanService)
    {
        $this->penerimaanService = $penerimaanService;
    }
    public function getAllCheckedPenerimaan(Request $request)
    {
        $filters = $request->only(['per_page', 'search']);
        $data = $this->penerimaanService->getPenerimaanList($filters, ['pending', 'checked'], 'check');
        return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil diambil', $data, 200);
    }
    public function updateKelayakanBarang(Request $request, $penerimaanId, $detailId)
    {
        $validated = $request->validate([
            'is_layak' => ['required', 'boolean'],
        ]);

        $data = $this->penerimaanService
            ->updateKelayakanBarang($penerimaanId, $detailId, $validated);

        return ResponseHelper::jsonResponse(
            true,
            'Status kelayakan diperbarui',
            $data,
            200
        );
    }
}
