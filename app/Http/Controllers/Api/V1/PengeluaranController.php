<?php

namespace App\Http\Controllers\Api\V1;

use App\Exports\PengeluaranExport;
use App\Helpers\GenerateFilenamePengeluaranExcel;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AlokasiStokGudangRequest;
use App\Http\Requests\V1\ExportExcelPengeluaran;
use App\Repositories\V1\PengeluaranRepository;
use Exception;
use App\Services\V1\PengeluaranService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PengeluaranController extends Controller
{
    private PengeluaranService $pengeluaranService;
    private GenerateFilenamePengeluaranExcel $generatePengeluaranExcel;

    public function __construct(PengeluaranService $pengeluaranService, GenerateFilenamePengeluaranExcel $generatePengeluaranExcel)
    {
        $this->pengeluaranService = $pengeluaranService;
        $this->generatePengeluaranExcel = $generatePengeluaranExcel;
    }
    public function index(Request $request)
    {
        $filters = [
            'per_page' => $request->query('per_page'),
            'search' => $request->query('search'),

            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];

        $data = $this->pengeluaranService->getAllPengeluaran($filters);
        return ResponseHelper::jsonResponse(true, 'Data pengeluaran berhasil diambil', $data, 200);
    }
    public function alokasiStokGudang(AlokasiStokGudangRequest $request, int $pemesananId)
    {
        $detail = $this->pengeluaranService->processGudangFulfillmentByPemesanan(
            $pemesananId,
            $request->detailPemesanan
        );

        return ResponseHelper::jsonResponse(true, 'Data pengeluaran gudang berhasil dibuat', $detail, 200);
    }
    public function getAvailableBastStokById(int $stokId)
    {
        $detail = $this->pengeluaranService->getAvailableBastByStok($stokId);
        return ResponseHelper::jsonResponse(true, 'Data BAST yang tersedia berhasil diambil', $detail, 200);
    }
    public function exportExcel(ExportExcelPengeluaran $request)
    {
        $filters = $request->validated();
        $filename = $this->generatePengeluaranExcel->generateExportFilename($filters);

        return Excel::download(
            new PengeluaranExport(
                app(PengeluaranRepository::class),
                $filters
            ),
            $filename
        );
    }
}
