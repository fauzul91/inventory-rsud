<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AlokasiStokGudangRequest;
use Exception;
use App\Services\V1\PengeluaranService;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    private PengeluaranService $pengeluaranService;

    public function __construct(PengeluaranService $pengeluaranService)
    {
        $this->pengeluaranService = $pengeluaranService;
    }
    public function index(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->query('per_page'),
                'search' => $request->query('search'),
            ];

            $data = $this->pengeluaranService->getAllPengeluaran($filters);
            return ResponseHelper::jsonResponse(true, 'Data pemesanan berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
    public function alokasiStokGudang(AlokasiStokGudangRequest $request, int $pemesananId)
    {
        try {
            $detail = $this->pengeluaranService->processGudangFulfillmentByPemesanan(
                $pemesananId,
                $request->detailPemesanan
            );

            return ResponseHelper::jsonResponse(true, 'Data pengeluaran gudang berhasil dibuat', $detail, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
    public function getAvailableBastStokById(int $stokId)
    {
        try {
            $detail = $this->pengeluaranService->getAvailableBastByStok($stokId);
            return ResponseHelper::jsonResponse(true, 'Data BAST yang tersedia berhasil diambil', $detail, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
}
