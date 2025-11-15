<?php

namespace App\Interfaces\V1;

interface MonitoringRepositoryInterface
{
    public function getAllMonitorings(array $filters);
}