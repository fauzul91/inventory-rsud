<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StokUpdateRequest;
use App\Interfaces\V1\StokRepositoryInterface;
use App\Services\V1\StokService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Class StokController
 * Mengelola data persediaan barang (stok), termasuk pemantauan kuantitas,
 * pembaruan data stok, dan penelusuran riwayat BAST terkait item stok.
 * * @package App\Http\Controllers\Api\V1
 */
class StokController extends Controller
{
    /**
     * @var StokRepositoryInterface
     */
    private StokRepositoryInterface $stokRepository;

    /**
     * @var StokService
     */
    private StokService $stokService;

    /**
     * StokController constructor.
     * * @param StokRepositoryInterface $stokRepository
     * @param StokService $stokService
     */
    public function __construct(StokRepositoryInterface $stokRepository, StokService $stokService)
    {
        $this->stokRepository = $stokRepository;
        $this->stokService = $stokService;
    }

    /**
     * Mengambil daftar stok barang untuk keperluan dropdown/select, 
     * opsional berdasarkan kategori.
     * * @param Request $request
     * @return JsonResponse
     */
    public function getAllForSelect(Request $request): JsonResponse
    {
        $categoryId = $request->query('category_id');
        $stok = $this->stokRepository->getAllStoksForSelect($categoryId);
        
        return ResponseHelper::jsonResponse(true, 'Data stok berhasil diambil', $stok);
    }

    /**
     * Menampilkan daftar stok barang dengan filter kategori, pencarian, dan paginasi.
     * * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['per_page', 'category', 'search']);
        $data = $this->stokService->getAllStoks($filters);
        
        return ResponseHelper::jsonResponse(true, 'Data stok berhasil diambil', $data);
    }

    /**
     * Menampilkan detail informasi stok barang berdasarkan ID.
     * * @param mixed $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $data = $this->stokService->edit($id);
        
        return ResponseHelper::jsonResponse(true, 'Detail stok berhasil diambil', $data);
    }

    /**
     * Memperbarui informasi data stok barang yang sudah ada.
     * * @param StokUpdateRequest $request
     * @param mixed $id
     * @return JsonResponse
     */
    public function update(StokUpdateRequest $request, $id): JsonResponse
    {
        $data = $this->stokService->update($request->validated(), $id);
        
        return ResponseHelper::jsonResponse(true, 'Stok berhasil diupdate', $data);
    }

    /**
     * Mengambil detail riwayat kaitan BAST (Berita Acara Serah Terima) dengan item stok spesifik.
     * * @param mixed $id
     * @return JsonResponse
     */
    public function getDetailBastStockById($id): JsonResponse
    {
        $data = $this->stokService->getStockById($id);
        
        return ResponseHelper::jsonResponse(true, 'Riwayat BAST Stok berhasil diambil', $data);
    }
}