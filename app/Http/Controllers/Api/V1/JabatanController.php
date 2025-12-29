<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\CategoryStoreRequest;
use App\Http\Requests\V1\CategoryUpdateRequest;
use App\Http\Requests\V1\JabatanStoreRequest;
use App\Http\Requests\V1\JabatanUpdateRequest;
use App\Interfaces\V1\JabatanRepositoryInterface;
use App\Repositories\V1\JabatanRepository;
use Exception;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private JabatanRepository $jabatanRepository;

    public function __construct(JabatanRepositoryInterface $jabatanRepository)
    {
        $this->jabatanRepository = $jabatanRepository;
    }
    public function getAllForSelect()
    {
        try {
            $pegawai = $this->jabatanRepository->getAllForSelect();
            return ResponseHelper::jsonResponse(true, 'Data jabatan berhasil diambil', $pegawai, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }
}