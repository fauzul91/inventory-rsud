<?php

namespace App\Services\V1\Sso;

use App\Models\User;
use App\Services\V1\MonitoringService;

class SsoLogoutService
{
    public function __construct(
        private MonitoringService $monitoringService
    ) {
    }

    public function logout(?User $user, string $destination): string
    {
        if ($user) {
            $this->monitoringService->log(
                "{$user->name} telah logout!",
                $user->id
            );

            $user->tokens()->delete();
        }

        $ssoLogoutBaseUrl = config('services.sso.logout_url');

        return $ssoLogoutBaseUrl . '?redirect=' . urlencode($destination);
    }
}