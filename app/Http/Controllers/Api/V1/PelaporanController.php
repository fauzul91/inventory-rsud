<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\V1\PelaporanRepository;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;

/**
 * Class PelaporanController
 * Menangani pembuatan laporan statistik dashboard, penerimaan, dan pengeluaran barang.
 * * @package App\Http\Controllers\Api\V1
 */
class PelaporanController extends Controller
{
    /**
     * @var PelaporanRepository
     */
    private PelaporanRepository $pelaporanRepository;

    /**
     * PelaporanController constructor.
     * * @param PelaporanRepository $pelaporanRepository
     */
    public function __construct(PelaporanRepository $pelaporanRepository)
    {
        $this->pelaporanRepository = $pelaporanRepository;
    }

    /**
     * Mengambil ringkasan data statistik untuk halaman dashboard.
     * * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $data = $this->pelaporanRepository->getDashboardStats();
        return ResponseHelper::jsonResponse(true, 'Data dashboard berhasil diambil', $data);
    }

    /**
     * Mengambil tren data penerimaan barang bulanan berdasarkan tahun.
     * * @param Request $request
     * @return JsonResponse
     */
    public function penerimaanPerBulan(Request $request): JsonResponse
    {
        $year = $request->query('year', now()->year);
        $data = $this->pelaporanRepository->getPenerimaanPerBulan($year);

        return ResponseHelper::jsonResponse(true, "Data penerimaan barang tahun $year", $data);
    }

    /**
     * Mengambil tren data pengeluaran barang bulanan berdasarkan tahun.
     * * @param Request $request
     * @return JsonResponse
     */
    public function pengeluaranPerBulan(Request $request): JsonResponse
    {
        $year = $request->query('year', now()->year);
        $data = $this->pelaporanRepository->getPengeluaranPerBulan($year);

        return ResponseHelper::jsonResponse(true, "Data pengeluaran barang tahun $year", $data);
    }
}