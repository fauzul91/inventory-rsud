<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\MonitoringRepositoryInterface;
use App\Models\Monitoring;

class MonitoringRepository implements MonitoringRepositoryInterface
{
    public function getAllMonitorings(array $filters)
    {
        $query = Monitoring::query()->orderBy('created_at', 'desc');
        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('activity', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($qu) use ($search) {
                        $qu->where('name', 'like', "%{$search}%");
                    });
            });
        }
        $perPage = $filters['per_page'] ?? 10;
        $monitorings = $query->paginate($perPage);

        $monitorings->getCollection()->transform(function ($item) {
            return [
                'name' => $item->user->name,
                'waktu' => $item->time,
                'tanggal' => $item->date,
                'activity' => $item->activity,
            ];
        });

        return $monitorings;
    }
}