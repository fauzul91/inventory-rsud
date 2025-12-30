<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Interfaces\V1\PegawaiRepositoryInterface;
use App\Repositories\V1\PegawaiRepository;
use App\Http\Requests\V1\PegawaiStoreRequest;
use App\Http\Requests\V1\PegawaiUpdateRequest;
use Illuminate\Http\Request;

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
        return ResponseHelper::jsonResponse(true, 'Data pegawai berhasil diambil', $this->pegawaiRepository->getAllForSelect());
    }

    public function getPegawaiForProfile()
    {
        return ResponseHelper::jsonResponse(true, 'Data profil pegawai berhasil diambil', $this->pegawaiRepository->getAllPegawaiForProfile());
    }

    public function store(PegawaiStoreRequest $request)
    {
        return ResponseHelper::jsonResponse(true, 'Pegawai berhasil ditambahkan', $this->pegawaiRepository->create($request->validated()), 201);
    }

    public function show($id)
    {
        return ResponseHelper::jsonResponse(true, 'Detail pegawai berhasil diambil', $this->pegawaiRepository->findById($id));
    }

    public function update(PegawaiUpdateRequest $request, $id)
    {
        return ResponseHelper::jsonResponse(true, 'Data pegawai berhasil diperbarui', $this->pegawaiRepository->update($request->validated(), $id));
    }

    public function toggleStatus($id)
    {
        return ResponseHelper::jsonResponse(true, 'Status pegawai berhasil diperbarui', $this->pegawaiRepository->toggleStatus($id));
    }
}