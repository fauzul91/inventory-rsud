<?php

namespace App\Http\Controllers\Api\V1;

use App\Exports\PengeluaranExport;
use App\Helpers\GenerateFilenamePengeluaranExcel;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AlokasiStokGudangRequest;
use App\Http\Requests\V1\ExportExcelPengeluaran;
use App\Repositories\V1\PengeluaranRepository;
use App\Services\V1\PengeluaranService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class PengeluaranController
 * Mengelola proses pengeluaran barang dari gudang (fulfillment), alokasi stok berdasarkan BAST,
 * serta fitur ekspor laporan pengeluaran ke format Excel.
 * * @package App\Http\Controllers\Api\V1
 */
class PengeluaranController extends Controller
{
    /**
     * @var PengeluaranService
     */
    private PengeluaranService $pengeluaranService;

    /**
     * @var GenerateFilenamePengeluaranExcel
     */
    private GenerateFilenamePengeluaranExcel $generatePengeluaranExcel;

    /**
     * PengeluaranController constructor.
     * * @param PengeluaranService $pengeluaranService
     * @param GenerateFilenamePengeluaranExcel $generatePengeluaranExcel
     */
    public function __construct(
        PengeluaranService $pengeluaranService,
        GenerateFilenamePengeluaranExcel $generatePengeluaranExcel
    ) {
        $this->pengeluaranService = $pengeluaranService;
        $this->generatePengeluaranExcel = $generatePengeluaranExcel;
    }

    /**
     * Menampilkan daftar transaksi pengeluaran barang dengan filter tanggal dan pencarian.
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['per_page', 'search', 'start_date', 'end_date']);

        $data = $this->pengeluaranService->getAllPengeluaran($filters);

        return ResponseHelper::jsonResponse(true, 'Data pengeluaran berhasil diambil', $data);
    }

    /**
     * Memproses alokasi stok gudang untuk memenuhi (fulfillment) permintaan pemesanan.
     * @return JsonResponse
     */
    public function alokasiStokGudang(AlokasiStokGudangRequest $request, int $pemesananId): JsonResponse
    {
        $detail = $this->pengeluaranService->processGudangFulfillmentByPemesanan(
            $pemesananId,
            $request->detailPemesanan
        );

        return ResponseHelper::jsonResponse(true, 'Data pengeluaran gudang berhasil dibuat', $detail);
    }

    /**
     * Mengambil daftar BAST yang tersedia dan memiliki sisa stok untuk item tertentu.
     * @return JsonResponse
     */
    public function getAvailableBastStokById(int $stokId): JsonResponse
    {
        $detail = $this->pengeluaranService->getAvailableBastByStok($stokId);

        return ResponseHelper::jsonResponse(true, 'Data BAST yang tersedia berhasil diambil', $detail);
    }

    /**
     * Mengekspor data laporan pengeluaran barang ke dalam format file Excel.
     * @return BinaryFileResponse
     */
    public function exportExcel(ExportExcelPengeluaran $request): BinaryFileResponse
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