<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Interfaces\V1\PegawaiRepositoryInterface;
use App\Repositories\V1\PegawaiRepository;
use Exception;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    private PegawaiRepository $pegawaiRepository;

    public function __construct(PegawaiRepositoryInterface $pegawaiRepository)
    {
        $this->pegawaiRepository = $pegawaiRepository;
    }
    public function getAllForSelect()
    {   
        try {
            $pegawai = $this->pegawaiRepository->getAllForSelect();
            return ResponseHelper::jsonResponse(true, 'Data pegawai berhasil diambil', $pegawai, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }
}
