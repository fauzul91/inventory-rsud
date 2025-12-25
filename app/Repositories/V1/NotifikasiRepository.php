<?php

namespace App\Repositories\V1;

use App\Models\Notifikasi;

class NotifikasiRepository
{
    public function getByUserId(array $filters, int $userId)
    {
        $query = Notifikasi::where('user_id', $userId);

        if (isset($filters['status'])) {
            if ($filters['status'] === 'unread') {
                $query->whereNull('read_at');
            } elseif ($filters['status'] === 'read') {
                $query->whereNotNull('read_at');
            }
        }
        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 10);
    }

    public function getUnreadCount(int $userId): int
    {
        return Notifikasi::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function markAsRead(int $id, int $userId): bool
    {
        return Notifikasi::where('id', $id)
            ->where('user_id', $userId)
            ->update(['read_at' => now()]);
    }

    public function markAllRead(int $userId): bool
    {
        return Notifikasi::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
    public function delete(int $id, int $userId): bool
    {
        return Notifikasi::where('id', $id)
            ->where('user_id', $userId)
            ->delete();
    }

    public function deleteAll(int $userId): bool
    {
        return Notifikasi::where('user_id', $userId)
            ->delete();
    }
}