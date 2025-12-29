<?php

namespace App\Services\V1;

use App\Models\Monitoring;
use Carbon\Carbon;

class MonitoringService
{
    public function log($activity, $userId = null)
    {
        $now = Carbon::now('Asia/Jakarta');

        Monitoring::create([
            'user_id' => $userId,
            'date' => $now->toDateString(),
            'time' => $now->toTimeString(),
            'activity' => $activity,
        ]);
    }
}
