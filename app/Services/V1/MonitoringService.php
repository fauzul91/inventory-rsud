<?php

namespace App\Services\V1;

use App\Models\Monitoring;

class MonitoringService
{
    public function log($activity, $userId = null)
    {
        Monitoring::create([
            'user_id' => $userId,
            'date' => now()->format('Y-m-d'),
            'time' => now()->format('H:i:s'),
            'activity' => $activity,
        ]);
    }
}
