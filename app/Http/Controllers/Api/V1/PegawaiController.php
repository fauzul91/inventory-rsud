<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PegawaiStoreRequest;
use App\Http\Requests\V1\PegawaiUpdateRequest;
use App\Repositories\V1\PegawaiRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Class PegawaiController
 * Mengelola data master pegawai, profil, dan status keaktifan.
 * * @package App\Http\Controllers\Api\V1
 */
class PegawaiController extends Controller
{
    /**
     * @var PegawaiRepository
     */
    private PegawaiRepository $pegawaiRepository;

    /**
     * PegawaiController constructor.
     * * @param PegawaiRepository $pegawaiRepository
     */
    public function __construct(PegawaiRepository $pegawaiRepository)
    {
        // Menggunakan interface agar sesuai dengan binding di AppServiceProvider
        $this->pegawaiRepository = $pegawaiRepository;
    }

    /**
     * Mengambil daftar pegawai dengan filter pencarian, jabatan, dan status.
     * * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'jabatan_id', 'status', 'per_page']);

        return ResponseHelper::jsonResponse(
            true,
            'Data pegawai berhasil diambil',
            $this->pegawaiRepository->getAll($filters)
        );
    }

    /**
     * Mengambil daftar minimalis pegawai untuk dropdown/select.
     * * @return JsonResponse
     */
    public function getAllForSelect(): JsonResponse
    {
        return ResponseHelper::jsonResponse(
            true,
            'Data pegawai berhasil diambil',
            $this->pegawaiRepository->getAllForSelect()
        );
    }

    /**
     * Mengambil data spesifik pegawai untuk profil.
     * * @return JsonResponse
     */
    public function getPegawaiForProfile(): JsonResponse
    {
        return ResponseHelper::jsonResponse(
            true,
            'Data profil pegawai berhasil diambil',
            $this->pegawaiRepository->getAllPegawaiForProfile()
        );
    }

    /**
     * Menyimpan data pegawai baru ke sistem.
     * * @param PegawaiStoreRequest $request
     * @return JsonResponse
     */
    public function store(PegawaiStoreRequest $request): JsonResponse
    {
        return ResponseHelper::jsonResponse(
            true,
            'Pegawai berhasil ditambahkan',
            $this->pegawaiRepository->create($request->validated()),
            201
        );
    }

    /**
     * Menampilkan detail lengkap seorang pegawai berdasarkan ID.
     * * @param mixed $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        return ResponseHelper::jsonResponse(
            true,
            'Detail pegawai berhasil diambil',
            $this->pegawaiRepository->findById($id)
        );
    }

    /**
     * Memperbarui informasi data pegawai yang sudah ada.
     * * @param PegawaiUpdateRequest $request
     * @param mixed $id
     * @return JsonResponse
     */
    public function update(PegawaiUpdateRequest $request, $id): JsonResponse
    {
        return ResponseHelper::jsonResponse(
            true,
            'Data pegawai berhasil diperbarui',
            $this->pegawaiRepository->update($request->validated(), $id)
        );
    }

    /**
     * Mengubah status aktif/non-aktif pegawai.
     * * @param mixed $id
     * @return JsonResponse
     */
    public function toggleStatus($id): JsonResponse
    {
        return ResponseHelper::jsonResponse(
            true,
            'Status pegawai berhasil diperbarui',
            $this->pegawaiRepository->toggleStatus($id)
        );
    }
}