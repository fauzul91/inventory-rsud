<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Interfaces\V1\StokRepositoryInterface;
use App\Repositories\V1\StokRepository;
use Exception;
use Illuminate\Http\Request;

class StokController extends Controller
{
    private StokRepository $stokRepository;

    public function __construct(StokRepositoryInterface $stokRepository)
    {
        $this->stokRepository = $stokRepository;
    }
    public function getAllForSelect()
    {   
        try {
            $satuan = $this->stokRepository->getAllStoksForSelect();
            return ResponseHelper::jsonResponse(true, 'Data stok berhasil diambil', $satuan, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }
}
