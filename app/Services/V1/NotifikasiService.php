<?php

namespace App\Services\V1;

use App\Models\Notifikasi;
use App\Models\User;
use App\Enum\V1\NotificationType;
use App\Enum\V1\RoleName;

class NotifikasiService
{
    public function penerimaanDiajukan($penerimaan): void
    {
        $this->notifyByRole(
            RoleName::TIM_TEKNIS,
            NotificationType::PENERIMAAN_DIAJUKAN,
            'Penerimaan Baru',
            "Tim PPK mengajukan BAST {$penerimaan->no_surat}, mohon lakukan pengecekan barang.",
            [
                'penerimaan_id' => $penerimaan->id,
                'no_surat' => $penerimaan->no_surat,
            ]
        );
    }

    public function uploadTTDPenerimaan($penerimaan): void
    {
        $this->notifyByRole(
            RoleName::ADMIN_GUDANG,
            NotificationType::UPLOAD_TTD_PENERIMAAN,
            'TTD BAST',
            "Harap lakukan tanda tangan untuk penerimaan {$penerimaan->no_surat}",
            [
                'penerimaan_id' => $penerimaan->id,
            ]
        );
    }

    public function stokMenipis($barang, int $stok): void
    {
        $this->notifyByRole(
            [
                RoleName::ADMIN_GUDANG,
                RoleName::PENANGGUNG_JAWAB
            ],
            NotificationType::STOK_MENIPIS,
            'Stok Menipis',
            "Stok {$barang->nama} tersisa {$stok}. Segera lakukan restock.",
            [
                'barang_id' => $barang->id,
                'stok' => $stok,
            ]
        );
    }

    private function notifyByRole(
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
            $this->createNotification(
                $user->id,
                $type,
                $title,
                $message,
                $data
            );
        }
    }

    private function createNotification(
        int $userId,
        NotificationType $type,
        string $title,
        string $message,
        array $data = []
    ): void {
        Notifikasi::create([
            'user_id' => $userId,
            'type' => $type->value,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function completePenerimaan(int $penerimaanId): void
    {
        Notifikasi::where('type', NotificationType::PENERIMAAN_DIAJUKAN->value)
            ->whereJsonContains('data->penerimaan_id', $penerimaanId)
            ->whereNull('completed_at')
            ->update([
                'completed_at' => now()
            ]);
    }
}
