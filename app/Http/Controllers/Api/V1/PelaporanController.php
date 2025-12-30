<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Repositories\V1\PelaporanRepository;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;

class PelaporanController extends Controller
{
    private $pelaporanRepository;

    public function __construct(PelaporanRepository $pelaporanRepository)
    {
        $this->pelaporanRepository = $pelaporanRepository;
    }

    public function index()
    {
        $data = $this->pelaporanRepository->getDashboardStats();
        return ResponseHelper::jsonResponse(true, 'Data dashboard berhasil diambil', $data);
    }

    public function penerimaanPerBulan(Request $request)
    {
        $year = $request->query('year', now()->year);
        $data = $this->pelaporanRepository->getPenerimaanPerBulan($year);

        return ResponseHelper::jsonResponse(true, "Data penerimaan barang tahun $year", $data);
    }

    public function pengeluaranPerBulan(Request $request)
    {
        $year = $request->query('year', now()->year);
        $data = $this->pelaporanRepository->getPengeluaranPerBulan($year);

        return ResponseHelper::jsonResponse(true, "Data pengeluaran barang tahun $year", $data);
    }
}