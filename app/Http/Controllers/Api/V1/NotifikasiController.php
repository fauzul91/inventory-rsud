<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\V1\NotifikasiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

/**
 * Class NotifikasiController
 * Mengelola notifikasi pengguna termasuk membaca, menghapus, dan rekap data.
 * * @package App\Http\Controllers\Api\V1
 */
class NotifikasiController extends Controller
{
    /**
     * @var NotifikasiService
     */
    private NotifikasiService $notifikasiService;

    /**
     * NotifikasiController constructor.
     * * @param NotifikasiService $notifikasiService
     */
    public function __construct(NotifikasiService $notifikasiService)
    {
        $this->notifikasiService = $notifikasiService;
    }

    /**
     * Mengambil daftar notifikasi pengguna beserta jumlah yang belum dibaca.
     * * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'per_page']);

        $result = $this->notifikasiService->getUserNotifications($filters, Auth::id());

        return ResponseHelper::jsonResponse(true, 'Data notifikasi berhasil diambil', [
            'unread_count' => $result['unread_count'],
            'list' => $result['list']
        ]);
    }

    /**
     * Menandai satu notifikasi spesifik sebagai sudah dibaca.
     * * @param int $id
     * @return JsonResponse
     */
    public function markAsRead(int $id): JsonResponse
    {
        $data = $this->notifikasiService->markNotificationAsRead($id, Auth::id());
        return ResponseHelper::jsonResponse(true, 'Notifikasi berhasil ditandai sebagai dibaca', $data);
    }

    /**
     * Menandai seluruh notifikasi pengguna sebagai sudah dibaca.
     * * @return JsonResponse
     */
    public function markAllAsRead(): JsonResponse
    {
        $data = $this->notifikasiService->markAllNotificationsAsRead(Auth::id());
        return ResponseHelper::jsonResponse(true, 'Semua notifikasi berhasil ditandai sebagai dibaca', $data);
    }

    /**
     * Menghapus satu notifikasi berdasarkan ID.
     * * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->notifikasiService->deleteNotification($id, Auth::id());
        return ResponseHelper::jsonResponse(true, 'Notifikasi berhasil dihapus', null);
    }

    /**
     * Menghapus seluruh daftar notifikasi milik pengguna.
     * * @return JsonResponse
     */
    public function destroyAll(): JsonResponse
    {
        $this->notifikasiService->deleteAllNotifications(Auth::id());
        return ResponseHelper::jsonResponse(true, 'Semua notifikasi berhasil dikosongkan', null);
    }
}