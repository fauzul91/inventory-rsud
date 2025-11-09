<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\UnauthorizedException;

class AuthController extends Controller
{
        public function index(Request $request)
        {
            $request->session()->put('state', $state = Str::random(40));

            $query = http_build_query([
                'client_id'     => env('PASSPORT_CLIENT_ID'),
                'redirect_uri'  => env('PASSPORT_CLIENT_CALLBACK_PATH'),
                'response_type' => 'code',
                'scope'         => '',
                'state'         => $state,
            ]);

            return redirect(env('SSO_URL') . '/oauth/authorize?' . $query);
        }

    public function ssoCallback(Request $request)
    {
        $state = $request->session()->pull('state');
        throw_unless(
            strlen($state) > 0 && $state === $request->state,
            \InvalidArgumentException::class
        );

        $response = Http::asForm()->post(env('SSO_URL') . '/oauth/token', [
            'grant_type'    => 'authorization_code',
            'client_id'     => env('PASSPORT_CLIENT_ID'),
            'client_secret' => env('PASSPORT_CLIENT_SECRET'),
            'redirect_uri'  => env('PASSPORT_CLIENT_CALLBACK_PATH'),
            'code'          => $request->code,
        ]);

        $tokenData = $response->json();

        if (!isset($tokenData['access_token'])) {
            return response()->json([
                'error' => 'Failed to get access token',
                'details' => $tokenData,
            ], 400);
        }

        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');

        return redirect()->away($frontendUrl . '/auth/callback?token=' . urlencode($tokenData['access_token']));
    }
}