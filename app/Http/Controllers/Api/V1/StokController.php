<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StokUpdateRequest;
use App\Interfaces\V1\StokRepositoryInterface;
use App\Repositories\V1\StokRepository;
use App\Services\V1\PenerimaanService;
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
        $categoryId = $request->query('category_id');
        $stok = $this->stokRepository->getAllStoksForSelect($categoryId);
        return ResponseHelper::jsonResponse(true, 'Data stok berhasil diambil', $stok, 200);
    }
    public function index(Request $request)
    {
        $filters = $request->only(['per_page', 'category', 'search']);
        $data = $this->stokService->getAllStoks($filters);
        return ResponseHelper::jsonResponse(true, 'Data stok berhasil diambil', $data, 200);
    }
    public function show($id)
    {
        $data = $this->stokService->edit($id);
        return ResponseHelper::jsonResponse(true, 'Detail stok berhasil diambil', $data, 200);
    }
    public function update(StokUpdateRequest $request, $id)
    {
        $data = $this->stokService->update($request->validated(), $id);
        return ResponseHelper::jsonResponse(true, 'Stok berhasil diupdate', $data, 200);
    }
    public function getDetailBastStockById($id)
    {
        $data = $this->stokService->getStockById($id);
        return ResponseHelper::jsonResponse(true, 'Riwayat BAST Stok berhasil diambil', $data, 200);
    }
}
