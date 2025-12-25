<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SsoController extends Controller
{
    public function redirectToSso()
    {
        $query = http_build_query([
            'client_id' => config('services.sso.client_id'),
            'redirect_uri' => config('services.sso.redirect'),
            'response_type' => 'code',
            'scope' => '',
            // 'prompt'        => 'none',
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
        $user = User::updateOrCreate(
            ['email' => $ssoUser['email']],
            [
                'sso_user_id' => $ssoUser['id'],
                'name' => $ssoUser['name'],
            ]
        );
        // $user = User::where('sso_user_id', $ssoUser['id'])
        //     ->orWhere('email', $ssoUser['email'])
        //     ->first();

        // if (!$user) {
        //     $user = User::create([
        //         'sso_user_id' => $ssoUser['id'],
        //         'name' => $ssoUser['name'],
        //         'email' => $ssoUser['email'],
        //     ]);
        // } else {
        //     $user->update([
        //         'sso_user_id' => $ssoUser['id'],
        //         'name' => $ssoUser['name'],
        //         'email' => $ssoUser['email'],
        //     ]);
        // }
        $user->tokens()->delete();
        $token = $user->createToken('sso-login')->plainTextToken;

        return redirect()->away(
            env('FRONTEND_URL') . '/auth/sso-callback?' . http_build_query([
                'token' => $token,
            ])
        );
        // Auth::login($user);

        // $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

        // $userData = [
        //     'id'    => $user->id,
        //     'name'  => $user->name,
        //     'email' => $user->email,
        //     'photo' => $user->photo ?? null,

        //     'role'  => $ssoUser['roles'][0] ?? 'guest',
        // ];

        // $query = http_build_query([
        //     'token' => $accessToken,
        //     'user'  => json_encode($userData),
        // ]);

        // return redirect()->away(
        //     env('FRONTEND_URL') . '/auth/sso-callback?' . $query
        // );
        // dd(env('FRONTEND_URL'));
    }

    public function logout()
    {
        Auth::logout();
        session()->flush();

        $ssoLogoutBaseUrl = config('services.sso.logout_url');

        $destination = config('services.sso.host');

        $targetUrl = $ssoLogoutBaseUrl . '?redirect=' . urlencode($destination);

        return redirect($targetUrl);
    }
}
