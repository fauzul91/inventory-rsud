<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Interfaces\V1\PelaporanRepositoryInterface;
use App\Repositories\V1\RepositoryRepository;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;


class PelaporanController extends Controller
{
    protected $pelaporanRepository;

    public function __construct(PelaporanRepositoryInterface $pelaporanRepository)
    {
        $this->pelaporanRepository = $pelaporanRepository;
    }

    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Data dashboard berhasil diambil',
            'data' => [
                'total_stok_barang' => $this->pelaporanRepository->getTotalStokBarang(),
                'bast_sudah_diterima' => $this->pelaporanRepository->getTotalBastSigned(),
                'barang_belum_dibayar' => $this->pelaporanRepository->getTotalBarangBelumDibayar(),
            ]
        ], 200);
    }

    public function penerimaanPerBulan(Request $request)
    {
        $year = $request->year ?? now()->year;

        $data = $this->pelaporanRepository->getPenerimaanPerBulan($year);

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $found = $data->firstWhere('month', $m);
            $result[] = [
                'month' => $m,
                'total' => $found->total ?? 0,
            ];
        }

        return ResponseHelper::jsonResponse(
            true,
            "Data penerimaan barang per bulan tahun $year",
            $result,
            200
        );
    }

    public function pengeluaranPerBulan(Request $request)
    {
        $year = $request->year ?? now()->year;

        $data = $this->pelaporanRepository->getPengeluaranPerBulan($year);

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $found = $data->firstWhere('month', $m);
            $result[] = [
                'month' => $m,
                'total' => $found->total ?? 0,
            ];
        }

        return ResponseHelper::jsonResponse(
            true,
            "Data pengeluaran barang per bulan tahun $year",
            $result,
            200
        );
    }
}
