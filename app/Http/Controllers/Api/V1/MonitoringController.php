<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Interfaces\V1\MonitoringRepositoryInterface;
use App\Repositories\V1\MonitoringRepository;
use Exception;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    private MonitoringRepository $monitoringRepository;

    public function __construct(MonitoringRepositoryInterface $monitoringRepository)
    {
        $this->monitoringRepository = $monitoringRepository;
    }
    public function index(Request $request)
    {
        $filters = [
            'per_page' => $request->query('per_page'),
            'search' => $request->query('search'),
        ];

        $categories = $this->monitoringRepository->getAllMonitorings($filters);
        return ResponseHelper::jsonResponse(true, 'Data monitoring berhasil diambil', $categories, 200);
    }
}
