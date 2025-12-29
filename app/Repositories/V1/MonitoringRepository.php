<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\MonitoringRepositoryInterface;
use App\Models\Monitoring;

class MonitoringRepository implements MonitoringRepositoryInterface
{
    public function getAllMonitorings(array $filters)
    {
        $query = Monitoring::query()->orderBy('created_at', 'desc');
        $perPage = $filters['per_page'] ?? 10;
        $monitorings = $query->paginate($perPage);

        $monitorings->getCollection()->transform(function ($item) {
            return [
                'foto' => $item->user->photo ? asset('storage/' . $item->user->photo) : null,
                'role' => $item->user ? $item->user->getRoleNames()->join(',') : 'Super Admin',
                'name' => $item->user->name,
                'waktu' => $item->time,
                'tanggal' => $item->date,
                'activity' => $item->activity,
            ];
        });

        return $monitorings;
    }
}