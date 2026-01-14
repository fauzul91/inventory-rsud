<?php

namespace App\Services\V1;

use App\Models\Notifikasi;
use App\Models\User;
use App\Enum\V1\NotificationType;
use App\Enum\V1\RoleName;
use App\Repositories\V1\NotifikasiRepository;

class NotifikasiService
{
    protected $notifikasiRepository;

    public function __construct(NotifikasiRepository $notifikasiRepository)
    {
        $this->notifikasiRepository = $notifikasiRepository;
    }

    public function getUserNotifications(array $filters, int $userId)
    {
        $notifications = $this->notifikasiRepository->getByUserId($filters, $userId);
        $unreadCount = $this->notifikasiRepository->getUnreadCount($userId);

        $notifications->getCollection()->transform(function ($notif) {
            return [
                'id' => $notif->id,
                'sender' => $notif->sender,
                'title' => $notif->title,
                'message' => $notif->message,
                'date' => $notif->created_at->translatedFormat('d M Y, H:i'),
                'isRead' => !is_null($notif->read_at),
                'type' => $notif->type,
                'url' => $notif->url,
            ];
        });

        return [
            'unread_count' => $unreadCount,
            'list' => $notifications
        ];
    }
    public function markNotificationAsRead(int $id, int $userId)
    {
        return $this->notifikasiRepository->markAsRead($id, $userId);
    }
    public function markAllNotificationsAsRead(int $userId)
    {
        return $this->notifikasiRepository->markAllRead($userId);
    }
    public function deleteNotification(int $id, int $userId)
    {
        return $this->notifikasiRepository->delete($id, $userId);
    }
    public function deleteAllNotifications(int $userId)
    {
        return $this->notifikasiRepository->deleteAll($userId);
    }
    public function penerimaanDiajukan($penerimaan, string $senderName): void
    {
        $this->notifyByRole(
            $senderName,
            RoleName::TIM_TEKNIS,
            NotificationType::PENERIMAAN_DIAJUKAN,
            'Pengecekan Barang',
            "Lakukan pengecekan fisik untuk pengajuan BAST No. {$penerimaan->no_surat} dari {$senderName}.",
            ['penerimaan_id' => $penerimaan->id]
        );
    }
    public function uploadTTDPenerimaan($penerimaan, string $senderName): void
    {
        $this->notifyByRole(
            $senderName,
            RoleName::ADMIN_GUDANG,
            NotificationType::UPLOAD_TTD_PENERIMAAN,
            'Tanda Tangan BAST',
            "Selesaikan tanda tangan untuk BAST No. {$penerimaan->no_surat} yang telah diverifikasi oleh {$senderName}.",
            ['penerimaan_id' => $penerimaan->id]
        );
    }
    public function pemesananDiajukan($pemesanan, string $senderName): void
    {
        $this->notifyByRole(
            $senderName,
            RoleName::PENANGGUNG_JAWAB,
            NotificationType::PEMESANAN_DIAJUKAN,
            'Persetujuan Pemesanan',
            "Periksa dan setujui kuantitas pemesanan ID #{$pemesanan->id} yang diajukan oleh {$senderName}.",
            ['pemesanan_id' => $pemesanan->id]
        );
    }
    public function konfirmasiPemesananAdmin($pemesanan, string $senderName): void
    {
        $this->notifyByRole(
            $senderName,
            RoleName::ADMIN_GUDANG,
            NotificationType::KONFIRMASI_PEMESANAN_ADMIN,
            'Pengeluaran Barang',
            "Segera konfirmasi pemesanan #{$pemesanan->id} dan pengeluaran barang untuk {$senderName}.",
            ['pemesanan_id' => $pemesanan->id]
        );
    }
    private function notifyByRole(
        string $sender,
        RoleName|array $roles,
        NotificationType $type,
        string $title,
        string $message,
        array $data = []
    ): void {
        $roleNames = collect(is_array($roles) ? $roles : [$roles])
            ->map(fn(RoleName $role) => $role->value)
            ->toArray();

        $users = User::role($roleNames)->get();

        foreach ($users as $user) {
            $this->createNotification($sender, $user->id, $type, $title, $message, $data);
        }
    }
    private function createNotification(
        string $sender,
        int $userId,
        NotificationType $type,
        string $title,
        string $message,
        array $data = []
    ): void {
        Notifikasi::create([
            'sender' => $sender,
            'user_id' => $userId,
            'type' => $type->value,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function completeNotification(NotificationType $type, $dataValue): void
    {
        $dataKey = match ($type) {
            NotificationType::PENERIMAAN_DIAJUKAN,
            NotificationType::UPLOAD_TTD_PENERIMAAN => 'penerimaan_id',
            NotificationType::PEMESANAN_DIAJUKAN,
            NotificationType::KONFIRMASI_PEMESANAN_ADMIN => 'pemesanan_id',
            default => 'id',
        };

        Notifikasi::where('type', $type->value)
            ->whereJsonContains("data->$dataKey", (int) $dataValue)
            ->whereNull('completed_at')
            ->update(['completed_at' => now()]);
    }
}