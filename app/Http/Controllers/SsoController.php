<?php

namespace App\Http\Controllers;

use App\Services\V1\MonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SsoController extends Controller
{
    private MonitoringService $monitoringService;

    public function __construct(
        MonitoringService $monitoringService,
    ) {
        $this->monitoringService = $monitoringService;
    }
    public function redirectToSso()
    {
        $query = http_build_query([
            'client_id' => config('services.sso.client_id'),
            'redirect_uri' => config('services.sso.redirect'),
            'response_type' => 'code',
            'scope' => '',
            'prompt' => 'login', // <--- WAJIB ADA INI BANG!
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

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => $roleNameFromSso]);
        $user->syncRoles($roleNameFromSso);

        $user->tokens()->delete();
        $token = $user->createToken('sso-login')->plainTextToken;
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'photo' => $user->photo ?? null,
            'role' => $ssoUser['roles'][0] ?? 'guest',
        ];
        $this->monitoringService->log("{$user->name} telah login!", $user->id);
        return redirect()->away(
            env('FRONTEND_URL') . '/auth/sso-callback?' . http_build_query([
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
        $destination = env('FRONTEND_URL');

        $targetUrl = $ssoLogoutBaseUrl . '?redirect=' . urlencode($destination);

        return response()->json([
            'success' => true,
            'target_url' => $targetUrl
        ]);
    }
}
