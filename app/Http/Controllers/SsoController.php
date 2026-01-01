<?php

namespace App\Http\Controllers;

use App\Services\V1\MonitoringService;
use App\Services\V1\Sso\SsoLogoutService;
use App\Services\V1\Sso\SsoOAuthService;
use App\Services\V1\Sso\SsoRedirectService;
use App\Services\V1\Sso\SsoUserService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

/**
 * Class SsoController
 * Menangani proses Single Sign-On (SSO) termasuk pengalihan,
 * callback otentikasi, dan proses logout.
 */
class SsoController extends Controller
{
    /**
     * SsoController constructor.
     */
    public function __construct(
        private MonitoringService $monitoringService,
        private SsoRedirectService $ssoRedirectService,
        private SsoOAuthService $ssoOAuthService,
        private SsoUserService $ssoUserService,
        private SsoLogoutService $ssoLogoutService,
    ) {
    }

    /**
     * Mengalihkan pengguna ke halaman otorisasi server SSO.
     *
     * @return RedirectResponse
     */
    public function redirectToSso(): RedirectResponse
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

    /**
     * Menangani callback dari server SSO setelah otorisasi berhasil.
     *
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function handleCallback(Request $request): RedirectResponse|JsonResponse
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
        $cookie = cookie(
            'access_token',
            $result['token'],
            60 * 24,
            '/',
            config('session.domain'),
            config('session.secure'),
            true,
            false,
            'Lax'
        );

        $targetUrl = $this->ssoRedirectService->getSafeFrontendUrl();
        return redirect()->away($targetUrl)->withCookie($cookie);
    }

    /**
     * Melakukan proses logout pengguna dan memberikan URL redirect server SSO.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $targetUrl = $this->ssoLogoutService->logout(
            $request->user(),
            $this->ssoRedirectService->getSsoLogoutUrl()
        );
        $cookie = cookie()->forget('access_token');
        return response()->json([
            'success' => true,
            'target_url' => $targetUrl,
        ])->withCookie($cookie);
    }
}