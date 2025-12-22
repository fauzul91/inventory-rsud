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

        // --- MULAI PERUBAHAN DI SINI ---

        // 1. Ambil token dari respon SSO sebelumnya
        $accessToken = $tokenResponse->json()['access_token'];

        // 2. Siapkan URL Frontend
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

        // 1. Kita buat array baru (Custom Data)
        // Gabungkan data dari DB Lokal ($user) + Role dari SSO ($ssoUser)
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'photo' => $user->photo, // Jika ada

            // AMBIL ROLE DARI VARIABLE $ssoUser (Hasil API /api/me SSO)
            // Pastikan kuncinya 'role' (sesuai yang diminta Frontend)
            'role' => $ssoUser['roles'][0] ?? 'guest',
        ];

        // dd($ssoUser);

        // 2. Encode array buatan kita tadi, BUKAN $user mentah
        $query = http_build_query([
            'token' => $accessToken,
            'user' => json_encode($userData), // <--- Pakai $userData
        ]);

        return redirect($frontendUrl . '/auth/sso-callback?' . $query);
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
