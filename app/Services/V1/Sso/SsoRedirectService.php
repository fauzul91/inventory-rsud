<?php

namespace App\Services\V1\Sso;

use Illuminate\Support\Facades\Log;
class SsoRedirectService
{
    private array $allowedDomains = [
        'localhost',
        '127.0.0.1',
        'myfrontend-app.com',
    ];

    public function frontendCallback(array $payload): string
    {
        $baseUrl = config('app.frontend_url', 'http://localhost:5173');
        $host = parse_url($baseUrl, PHP_URL_HOST);

        if (!in_array($host, $this->allowedDomains)) {
            Log::warning("Unauthorized redirect host: {$host}");
            $baseUrl = 'http://localhost:8000';
        }

        return $baseUrl . '/auth/sso-callback?' . http_build_query($payload);
    }
    public function getSafeFrontendUrl(): string
    {
        $url = env('FRONTEND_URL', 'http://localhost:5173');
        $host = parse_url($url, PHP_URL_HOST);

        if (!in_array($host, $this->allowedDomains)) {
            Log::warning("Attempted redirect to unauthorized host: " . ($host ?? 'unknown'));
            return 'http://localhost:8000';
        }

        return $url;
    }
    public function getSsoLogoutUrl(): string
    {
        $ssoLogoutBaseUrl = config('services.sso.logout_url'); // Pastikan ini http://localhost:8000/logout
        $destination = $this->getSafeFrontendUrl();

        return rtrim($ssoLogoutBaseUrl, '/') . '?redirect=' . urlencode($destination);
    }
}