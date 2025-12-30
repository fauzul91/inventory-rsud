<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Repositories\V1\MonitoringRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Class MonitoringController
 * Menangani log aktivitas sistem dan monitoring user.
 * * @package App\Http\Controllers\Api\V1
 */
class MonitoringController extends Controller
{
    /**
     * @var MonitoringRepository
     */
    private MonitoringRepository $monitoringRepository;

    /**
     * MonitoringController constructor.
     * * @param MonitoringRepository $monitoringRepository
     */
    public function __construct(MonitoringRepository $monitoringRepository)
    {
        $this->monitoringRepository = $monitoringRepository;
    }

    /**
     * Mengambil daftar log monitoring dengan filtrasi dan paginasi.
     * * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['per_page', 'search']);

        return ResponseHelper::jsonResponse(
            true,
            'Data monitoring berhasil diambil',
            $this->monitoringRepository->getAllMonitorings($filters)
        );
    }
}