<?php

namespace App\Services\V1\Sso;

use Illuminate\Support\Facades\Http;
class SsoOAuthService
{
    public function getAccessToken(string $code): string
    {
        $response = Http::asForm()->post(
            config('services.sso.host') . '/oauth/token',
            [
                'grant_type' => 'authorization_code',
                'client_id' => config('services.sso.client_id'),
                'client_secret' => config('services.sso.secret'),
                'redirect_uri' => config('services.sso.redirect'),
                'code' => $code,
            ]
        );

        if ($response->failed()) {
            throw new \RuntimeException('Gagal mendapatkan access token');
        }

        return $response->json('access_token');
    }

    public function fetchUser(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->get(config('services.sso.host') . '/api/me');

        if ($response->failed()) {
            throw new \RuntimeException('Gagal mengambil data user dari SSO');
        }

        return $response->json();
    }
}
