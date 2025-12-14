<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StokUpdateRequest;
use App\Interfaces\V1\StokRepositoryInterface;
use App\Repositories\V1\StokRepository;
use App\Services\V1\StokService;
use Exception;
use Illuminate\Http\Request;

class StokController extends Controller
{
    private StokRepository $stokRepository;
    private StokService $stokService;

    public function __construct(StokRepositoryInterface $stokRepository, StokService $stokService)
    {
        $this->stokRepository = $stokRepository;
        $this->stokService = $stokService;
    }
    public function getAllForSelect(Request $request)
    {
        try {
            $categoryId = $request->query('category_id'); // bisa null
            $stok = $this->stokRepository->getAllStoksForSelect($categoryId);
            return ResponseHelper::jsonResponse(true, 'Data stok berhasil diambil', $stok, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }
    public function getAllYearForSelect()
    {
        try {
            $year = $this->stokService->getAllYearForSelect();
            return ResponseHelper::jsonResponse(true, 'Data year berhasil diambil', $year, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }
    public function index(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->query('per_page'),
                'category' => $request->query('category'),
                'search' => $request->query('search'),
                'year' => $request->query('year') ?? date('Y'), // default tahun sekarang
            ];

            $data = $this->stokService->getAllStoks($filters);
            return ResponseHelper::jsonResponse(true, 'Data stok berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }
    public function getPaidBastStock(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->query('per_page'),
                'category' => $request->query('category'),
                'search' => $request->query('search'),
            ];

            $data = $this->stokService->getPaidBastStock($filters);
            return ResponseHelper::jsonResponse(true, 'Data bast sudah dibayar berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }
    public function getUnpaidBastStock(Request $request)
    {
        try {
            $filters = [
                'per_page' => $request->query('per_page'),
                'category' => $request->query('category'),
                'search' => $request->query('search'),
            ];

            $data = $this->stokService->getUnpaidBastStock($filters);
            return ResponseHelper::jsonResponse(true, 'Data bast belum dibayar berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }
    public function show($id)
    {
        try {
            $data = $this->stokService->edit($id);
            return ResponseHelper::jsonResponse(true, 'Detail stok berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }
    public function update(StokUpdateRequest $request, $id)
    {
        try {
            $data = $this->stokService->update($request->validated(), $id);
            return ResponseHelper::jsonResponse(true, 'Stok berhasil diupdate', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }
    public function getDetailBastStockById($id)
    {
        try {
            $data = $this->stokService->getStockById($id);
            return ResponseHelper::jsonResponse(true, 'Riwayat BAST Stok berhasil diambil', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }
}
