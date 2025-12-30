<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Repositories\V1\JabatanRepository;
use Illuminate\Http\JsonResponse;

/**
 * Class JabatanController
 * Mengelola data jabatan pegawai.
 * * @package App\Http\Controllers\Api\V1
 */
class JabatanController extends Controller
{
    /**
     * @var JabatanRepository
     */
    private JabatanRepository $jabatanRepository;

    /**
     * JabatanController constructor.
     * * @param JabatanRepository $jabatanRepository
     */
    public function __construct(JabatanRepository $jabatanRepository)
    {
        $this->jabatanRepository = $jabatanRepository;
    }

    /**
     * Mengambil daftar semua jabatan untuk keperluan dropdown/select.
     * * @return JsonResponse
     */
    public function getAllForSelect(): JsonResponse
    {
        return ResponseHelper::jsonResponse(
            true, 
            'Data jabatan berhasil diambil', 
            $this->jabatanRepository->getAllForSelect()
        );
    }
}