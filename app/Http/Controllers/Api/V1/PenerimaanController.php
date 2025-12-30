<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\PenerimaanStoreRequest;
use App\Http\Requests\V1\PenerimaanUpdateRequest;
use App\Services\V1\PenerimaanService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Class PenerimaanController
 * Mengelola siklus hidup data penerimaan barang dari supplier/vendor,
 * mulai dari pencatatan, pembaruan, hingga penghapusan data.
 * * @package App\Http\Controllers\Api\V1
 */
class PenerimaanController extends Controller
{
    /**
     * @var PenerimaanService
     */
    private PenerimaanService $penerimaanService;

    /**
     * PenerimaanController constructor.
     * * @param PenerimaanService $penerimaanService
     */
    public function __construct(PenerimaanService $penerimaanService)
    {
        $this->penerimaanService = $penerimaanService;
    }

    /**
     * Menampilkan daftar penerimaan barang dengan filter dan paginasi.
     * * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['per_page', 'sort_by', 'search']);
        $data = $this->penerimaanService->getPenerimaanList($filters, ['pending']);

        return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil diambil', $data);
    }

    /**
     * Menyimpan data penerimaan barang baru ke database.
     * * @param PenerimaanStoreRequest $request
     * @return JsonResponse
     */
    public function store(PenerimaanStoreRequest $request): JsonResponse
    {
        $data = $this->penerimaanService->create($request->validated());

        return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil ditambahkan', $data, 201);
    }

    /**
     * Menampilkan detail data penerimaan barang berdasarkan ID untuk proses edit.
     * * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        $data = $this->penerimaanService->getPenerimaanForEdit($id);

        return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil diambil', $data);
    }

    /**
     * Memperbarui data penerimaan barang yang sudah ada.
     * * @param PenerimaanUpdateRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(PenerimaanUpdateRequest $request, string $id): JsonResponse
    {
        $data = $this->penerimaanService->updatePenerimaan($id, $request->validated());

        return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil diperbarui', $data);
    }

    /**
     * Menghapus data penerimaan barang dari sistem.
     * * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $this->penerimaanService->delete($id);

        return ResponseHelper::jsonResponse(true, 'Data penerimaan berhasil dihapus', null);
    }
}