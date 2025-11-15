<?php

namespace App\Repositories\V1;

use App\Interfaces\V1\MonitoringRepositoryInterface;
use App\Models\Monitoring;

class MonitoringRepository implements MonitoringRepositoryInterface
{
    public function getAllMonitorings(array $filters)
    {
        $query = Monitoring::query();

        if (!empty($filters['sort_by'])) {
            if ($filters['sort_by'] === 'latest') {
                $query->orderBy('created_at', 'desc'); 
            } elseif ($filters['sort_by'] === 'oldest') {
                $query->orderBy('created_at', 'asc');  
            }
        } else {
            $query->orderBy('date', 'asc')->orderBy('time', 'asc');
        }

        $perPage = $filters['per_page'] ?? 10;
        return $query->paginate($perPage);
    }
}