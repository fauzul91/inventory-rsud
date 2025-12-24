<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SsoController extends Controller
{
    /**
     * Arahkan user ke halaman login SSO.
     */
    public function redirectToSso()
    {
        $query = http_build_query([
            'client_id'     => config('services.sso.client_id'),
            'redirect_uri'  => config('services.sso.redirect'),
            'response_type' => 'code',
            'scope'         => '',
            // 'prompt'        => 'none',
        ]);

        return redirect(config('services.sso.host') . '/oauth/authorize?' . $query);
    }

    /**
     * Callback yang dipanggil setelah login SSO berhasil.
     * Di sini kita akan:
     * - Tukar authorization code jadi access token
     * - Ambil data user dari SSO (/api/me)
     * - Simpan user ke database client
     * - Login user ke sistem client
     */
    public function handleCallback(Request $request)
    {
        if (!$request->has('code')) {
            return response()->json(['error' => 'Kode otorisasi tidak ditemukan.'], 400);
        }

        // 1. Tukar code → access token
        $tokenResponse = Http::asForm()->post(
            config('services.sso.host') . '/oauth/token',
            [
                'grant_type'    => 'authorization_code',
                'client_id'     => config('services.sso.client_id'),
                'client_secret' => config('services.sso.secret'),
                'redirect_uri'  => config('services.sso.redirect'),
                'code'          => $request->code,
            ]
        );

        if ($tokenResponse->failed()) {
            return response()->json([
                'error'   => 'Gagal mendapatkan access token',
                'details' => $tokenResponse->json(),
            ], 500);
        }

        $accessToken = $tokenResponse->json()['access_token'];

        // 2. Ambil data user dari SSO
        $userResponse = Http::withToken($accessToken)
            ->get(config('services.sso.host') . '/api/me');

        if ($userResponse->failed()) {
            return response()->json([
                'error'   => 'Gagal mengambil data user dari SSO',
                'details' => $userResponse->json(),
            ], 500);
        }

        $ssoUser = $userResponse->json();

        // 3. Cari user lokal (AMAN: by sso_user_id ATAU email)
        $user = User::where('sso_user_id', $ssoUser['id'])
            ->orWhere('email', $ssoUser['email'])
            ->first();

        if (!$user) {
            $user = User::create([
                'sso_user_id' => $ssoUser['id'],
                'name'        => $ssoUser['name'],
                'email'       => $ssoUser['email'],
            ]);
        } else {
            $user->update([
                'sso_user_id' => $ssoUser['id'],
                'name'        => $ssoUser['name'],
                'email'       => $ssoUser['email'],
            ]);
        }

        // 4. Login user di backend
        Auth::login($user);

        // 5. Siapkan payload ke FE (SESUAI FE)
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

        $userData = [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'photo' => $user->photo ?? null,

            // FE kamu pakai `role` (string), bukan array
            'role'  => $ssoUser['roles'][0] ?? 'guest',
        ];

        // 6. Redirect ke FE callback
        $query = http_build_query([
            'token' => $accessToken,
            'user'  => json_encode($userData),
        ]);

        return redirect()->away(
            env('FRONTEND_URL') . '/auth/sso-callback?' . $query
        );


        dd(env('FRONTEND_URL'));
    }

    /**
     * Logout user dari client & arahkan juga ke logout SSO.
     */
    public function logout()
    {
        Auth::logout();
        session()->flush();

        // 1. Ambil URL Logout SSO
        $ssoLogoutBaseUrl = config('services.sso.logout_url');

        // 2. Tentukan Tujuan Akhir: "Kembalikan saya ke Rumah SSO (localhost:9000)"
        $destination = config('services.sso.host'); // http://localhost:9000

        // 3. Susun URL
        // Hasil: http://localhost:9000/logout?redirect=http://localhost:9000
        $targetUrl = $ssoLogoutBaseUrl . '?redirect=' . urlencode($destination);

        return redirect($targetUrl);
    }
}
