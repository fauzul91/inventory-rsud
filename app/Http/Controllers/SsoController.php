<?php

namespace App\Http\Controllers;

use App\Services\V1\MonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class SsoController extends Controller
{
    private MonitoringService $monitoringService;
    private array $allowedDomains = [
        'localhost',
        '127.0.0.1',
        'myfrontend-app.com',
    ];
    public function __construct(
        MonitoringService $monitoringService,
    ) {
        $this->monitoringService = $monitoringService;
    }

    private function getSafeFrontendUrl(): string
    {
        $url = env('FRONTEND_URL', 'http://localhost:3000');
        $host = parse_url($url, PHP_URL_HOST);

        if (!in_array($host, $this->allowedDomains)) {
            Log::warning("Attempted redirect to unauthorized host: " . ($host ?? 'unknown'));
            return 'http://localhost:8000';
        }

        return $url;
    }
    public function redirectToSso()
    {
        $query = http_build_query([
            'client_id' => config('services.sso.client_id'),
            'redirect_uri' => config('services.sso.redirect'),
            'response_type' => 'code',
            'scope' => '',
            'prompt' => 'login',
        ]);

        return redirect(config('services.sso.host') . '/oauth/authorize?' . $query);
    }

    public function handleCallback(Request $request)
    {
        if (!$request->has('code')) {
            return response()->json(['error' => 'Kode otorisasi tidak ditemukan.'], 400);
        }

        $tokenResponse = Http::asForm()->post(
            config('services.sso.host') . '/oauth/token',
            [
                'grant_type' => 'authorization_code',
                'client_id' => config('services.sso.client_id'),
                'client_secret' => config('services.sso.secret'),
                'redirect_uri' => config('services.sso.redirect'),
                'code' => $request->code,
            ]
        );

        if ($tokenResponse->failed()) {
            return response()->json([
                'error' => 'Gagal mendapatkan access token',
                'details' => $tokenResponse->json(),
            ], 500);
        }

        $accessToken = $tokenResponse->json()['access_token'];

        $userResponse = Http::withToken($accessToken)
            ->get(config('services.sso.host') . '/api/me');

        if ($userResponse->failed()) {
            return response()->json([
                'error' => 'Gagal mengambil data user dari SSO',
                'details' => $userResponse->json(),
            ], 500);
        }

        $ssoUser = $userResponse->json();
        $roleNameFromSso = $ssoUser['roles'][0] ?? 'guest';
        $user = User::updateOrCreate(
            ['sso_user_id' => $ssoUser['id']],
            [
                'email' => $ssoUser['email'],
                'name' => $ssoUser['name'],
            ]
        );

        Role::firstOrCreate(['name' => $roleNameFromSso]);
        $user->syncRoles($roleNameFromSso);

        $user->tokens()->delete();
        $token = $user->createToken('sso-login')->plainTextToken;
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $ssoUser['roles'][0] ?? 'guest',
        ];
        $this->monitoringService->log("{$user->name} telah login!", $user->id);
        $baseUrl = $this->getSafeFrontendUrl();

        return redirect()->away(
            $baseUrl . '/auth/sso-callback?' . http_build_query([
                'token' => $token,
                'user' => json_encode($userData),
            ])
        );
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $this->monitoringService->log("{$user->name} telah logout!", $user->id);
            $user->tokens()->delete();
        }

        $ssoLogoutBaseUrl = config('services.sso.logout_url');
        $destination = $this->getSafeFrontendUrl();

        $targetUrl = $ssoLogoutBaseUrl . '?redirect=' . urlencode($destination);

        return response()->json([
            'success' => true,
            'target_url' => $targetUrl
        ]);
    }
}
