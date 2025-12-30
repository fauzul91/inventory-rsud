<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Interfaces\V1\PegawaiRepositoryInterface;
use App\Repositories\V1\PegawaiRepository;
use App\Http\Requests\V1\PegawaiStoreRequest;
use App\Http\Requests\V1\PegawaiUpdateRequest;
use Exception;
use Illuminate\Http\Request;
use Throwable;

class PegawaiController extends Controller
{
    private PegawaiRepository $pegawaiRepository;

    public function __construct(PegawaiRepositoryInterface $pegawaiRepository)
    {
        $this->pegawaiRepository = $pegawaiRepository;
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->search,
            'jabatan_id' => $request->jabatan_id,
            'status' => $request->status,
            'per_page' => $request->per_page,
        ];

        $pegawai = $this->pegawaiRepository->getAll($filters);
        return ResponseHelper::jsonResponse(true, 'Data pegawai berhasil diambil', $pegawai, 200);
    }

    public function getAllForSelect()
    {
        $pegawai = $this->pegawaiRepository->getAllForSelect();
        return ResponseHelper::jsonResponse(true, 'Data pegawai berhasil diambil', $pegawai, 200);
    }
    public function getPegawaiForProfile()
    {
        $pegawai = $this->pegawaiRepository->getAllPegawaiForProfile();
        return ResponseHelper::jsonResponse(true, 'Data profil pegawai berhasil diambil', $pegawai, 200);
    }

    public function store(PegawaiStoreRequest $request)
    {
        $pegawai = $this->pegawaiRepository->create($request->validated());
        return ResponseHelper::jsonResponse(true, 'Pegawai berhasil ditambahkan', $pegawai, 201);
    }

    public function show($id)
    {
        $pegawai = $this->pegawaiRepository->findById($id);
        return ResponseHelper::jsonResponse(true, 'Detail pegawai berhasil diambil', $pegawai, 200);
    }

    public function update(PegawaiUpdateRequest $request, $id)
    {
        $pegawai = $this->pegawaiRepository->update($request->validated(), $id);
        return ResponseHelper::jsonResponse(true, 'Data pegawai berhasil diperbarui', $pegawai, 200);
    }

    public function toggleStatus($id)
    {
        $pegawai = $this->pegawaiRepository->toggleStatus($id);
        return ResponseHelper::jsonResponse(true, 'Status pegawai berhasil diperbarui', $pegawai, 200);
    }
}
