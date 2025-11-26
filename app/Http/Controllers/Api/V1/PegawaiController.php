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

class PegawaiController extends Controller
{
    private PegawaiRepository $pegawaiRepository;

    public function __construct(PegawaiRepositoryInterface $pegawaiRepository)
    {
        $this->pegawaiRepository = $pegawaiRepository;
    }

    public function index(Request $request)
    {
        try {
            $filters = [
                'search' => $request->search,
                'jabatan_id' => $request->jabatan_id,
                'status' => $request->status,
            ];

            $pegawai = $this->pegawaiRepository->getAll($filters);
            return ResponseHelper::jsonResponse(true, 'Data pegawai berhasil diambil', $pegawai, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
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

    public function store(PegawaiStoreRequest $request)
    {
        try {
            $pegawai = $this->pegawaiRepository->create($request->validated());
            return ResponseHelper::jsonResponse(true, 'Pegawai berhasil ditambahkan', $pegawai, 201);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Gagal menambahkan pegawai: ' . $e->getMessage(), null, 500);
        }
    }

    // ambil pegawai untuk detail/edit
    public function show($id)
    {
        try {
            $pegawai = $this->pegawaiRepository->findById($id);
            return ResponseHelper::jsonResponse(true, 'Detail pegawai berhasil diambil', $pegawai, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Data pegawai tidak ditemukan: ' . $e->getMessage(), null, 404);
        }
    }

    public function update(PegawaiUpdateRequest $request, $id)
    {
        try {
            $pegawai = $this->pegawaiRepository->update($request->validated(), $id);
            return ResponseHelper::jsonResponse(true, 'Data pegawai berhasil diperbarui', $pegawai, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Gagal memperbarui pegawai: ' . $e->getMessage(), null, 500);
        }
    }

    public function toggleStatus($id)
    {
        try {
            $pegawai = $this->pegawaiRepository->toggleStatus($id);
            return ResponseHelper::jsonResponse(true, 'Status pegawai berhasil diperbarui', $pegawai, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Gagal memperbarui status: ' . $e->getMessage(), null, 500);
        }
    }
}
