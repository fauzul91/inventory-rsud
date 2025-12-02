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
            'client_id' => config('services.sso.client_id'),
            'redirect_uri' => config('services.sso.redirect'),
            'response_type' => 'code',
            'scope' => '', // kosong jika tidak ada scope tambahan
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
        // Pastikan ada 'code' dari SSO
        if (!$request->has('code')) {
            return response()->json(['error' => 'Kode otorisasi tidak ditemukan.'], 400);
        }

        // Tukar authorization code menjadi access token
        $tokenResponse = Http::asForm()->post(config('services.sso.host') . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.sso.client_id'),
            'client_secret' => config('services.sso.secret'),
            'redirect_uri' => config('services.sso.redirect'),
            'code' => $request->code,
        ]);

        if ($tokenResponse->failed()) {
            return response()->json([
                'error' => 'Gagal mendapatkan access token.',
                'details' => $tokenResponse->json()
            ], 500);
        }

        $token = $tokenResponse->json()['access_token'];

        // Ambil data user dari endpoint /api/me di server SSO
        $userResponse = Http::withToken($token)
            ->get(config('services.sso.host') . '/api/me');

        if ($userResponse->failed()) {
            return response()->json([
                'error' => 'Gagal mengambil data user dari SSO.',
                'details' => $userResponse->json()
            ], 500);
        }

        $ssoUser = $userResponse->json();

        // Simpan atau update user di database client
        // $user = User::updateOrCreate(
        //     ['sso_user_id' => $ssoUser['id']],
        //     [
        //         'name' => $ssoUser['name'],
        //         'email' => $ssoUser['email'],
        //     ]
        // );

        $user = User::updateOrCreate(
            ['sso_user_id' => $ssoUser['id']],
            [
                'name' => $ssoUser['name'],
                'email' => $ssoUser['email'],
            ]
        );

        // Login user ke sistem client
        Auth::login($user);

        // Simpan role dari SSO ke session
        session(['roles' => $ssoUser['roles'] ?? []]);

        // Redirect ke dashboard atau halaman utama
        // return redirect('/dashboard');
        return response()->json([
            'message' => 'Login SSO berhasil!',
            'user' => $user,
            'roles' => session('roles'),
        ]);
    }

    /**
     * Logout user dari client & arahkan juga ke logout SSO.
     */
    public function logout()
    {
        Auth::logout();
        session()->flush();

        return redirect(config('services.sso.logout_url'));
    }
}
