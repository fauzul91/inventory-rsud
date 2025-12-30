<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\V1\NotifikasiService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    protected $notifikasiService;

    public function __construct(NotifikasiService $notifikasiService)
    {
        $this->notifikasiService = $notifikasiService;
    }
    public function index(Request $request)
    {
        $filters = [
            'status' => $request->query('status'),
            'per_page' => $request->query('per_page', 10)
        ];

        $result = $this->notifikasiService->getUserNotifications($filters, Auth::id());
        $data = [
            'unread_count' => $result['unread_count'],
            'list' => $result['list']
        ];
        return ResponseHelper::jsonResponse(true, 'Data notifikasi berhasil diambil', $data, 200);
    }
    public function markAsRead(int $id)
    {
        $data = $this->notifikasiService->markNotificationAsRead($id, Auth::id());
        return ResponseHelper::jsonResponse(true, 'Notifikasi berhasil ditandai sebagai dibaca', $data, 200);
    }

    public function markAllAsRead()
    {
        $data = $this->notifikasiService->markAllNotificationsAsRead(Auth::id());
        return ResponseHelper::jsonResponse(true, 'Semua notifikasi berhasil ditandai sebagai dibaca', $data, 200);
    }
    public function destroy(int $id)
    {
        $this->notifikasiService->deleteNotification($id, Auth::id());
        return ResponseHelper::jsonResponse(true, 'Notifikasi berhasil dihapus', null, 200);
    }
    public function destroyAll()
    {
        $this->notifikasiService->deleteAllNotifications(Auth::id());
        return ResponseHelper::jsonResponse(true, 'Semua notifikasi berhasil dikosongkan', null, 200);
    }
}
