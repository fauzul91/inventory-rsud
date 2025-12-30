<?php

namespace App\Http\Controllers;

use App\Services\V1\MonitoringService;
use App\Services\V1\Sso\SsoLogoutService;
use App\Services\V1\Sso\SsoOAuthService;
use App\Services\V1\Sso\SsoRedirectService;
use App\Services\V1\Sso\SsoUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class SsoController extends Controller
{
    public function __construct(
        private MonitoringService $monitoringService,
        private SsoRedirectService $ssoRedirectService,
        private SsoOAuthService $ssoOAuthService,
        private SsoUserService $ssoUserService,
        private SsoLogoutService $ssoLogoutService,
    ) {
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
            return response()->json(['error' => 'Kode otorisasi tidak ditemukan'], 400);
        }
        $accessToken = $this->ssoOAuthService->getAccessToken($request->code);
        $ssoUser = $this->ssoOAuthService->fetchUser($accessToken);
        $result = $this->ssoUserService->syncUser($ssoUser);

        $this->monitoringService->log(
            "{$result['user']['name']} telah login!",
            $result['user']['id']
        );
        return redirect()->away(
            $this->ssoRedirectService->frontendCallback([
                'token' => $result['token'],
                'user' => json_encode($result['user']),
            ])
        );
    }
    public function logout(Request $request)
    {
        $targetUrl = $this->ssoLogoutService->logout(
            $request->user(),
            $this->ssoRedirectService->getSafeFrontendUrl()
        );

        return response()->json([
            'success' => true,
            'target_url' => $targetUrl,
        ]);
    }
}
