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
        try {
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
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan ' . $e->getMessage(), null, 500);
        }
    }
    public function markAsRead(int $id)
    {
        try {
            $data = $this->notifikasiService->markNotificationAsRead($id, Auth::id());
            return ResponseHelper::jsonResponse(true, 'Notifikasi berhasil ditandai sebagai dibaca', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }

    public function markAllAsRead()
    {
        try {
            $data = $this->notifikasiService->markAllNotificationsAsRead(Auth::id());
            return ResponseHelper::jsonResponse(true, 'Semua notifikasi berhasil ditandai sebagai dibaca', $data, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage(), null, 500);
        }
    }
    public function destroy(int $id)
    {
        try {
            $this->notifikasiService->deleteNotification($id, Auth::id());
            return ResponseHelper::jsonResponse(true, 'Notifikasi berhasil dihapus', null, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Gagal menghapus notifikasi: ' . $e->getMessage(), null, 500);
        }
    }
    public function destroyAll()
    {
        try {
            $this->notifikasiService->deleteAllNotifications(Auth::id());
            return ResponseHelper::jsonResponse(true, 'Semua notifikasi berhasil dikosongkan', null, 200);
        } catch (Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Gagal mengosongkan notifikasi: ' . $e->getMessage(), null, 500);
        }
    }
}
