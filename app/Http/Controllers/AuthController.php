<?php

namespace App\Http\Controllers;

use App\Models\Monitoring;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\UnauthorizedException;

class AuthController extends Controller
{
    public function index(Request $request)
    {
        $request->session()->put('state', $state = Str::random(40));

        $clientId = trim(env('PASSPORT_CLIENT_ID'));
        $redirectUri = trim(env('PASSPORT_CLIENT_CALLBACK_PATH'));
        $sso = rtrim(trim(env('SSO_URL', '')), '/');

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => '', 
            'state' => $state,
        ]);

        $url = $sso . '/oauth/authorize?' . $query;
        return redirect()->away($url);
    }

    public function ssoCallback(Request $request)
    {
        $state = $request->session()->pull('state');
        throw_unless(
            strlen($state) > 0 && $state === $request->state,
            \InvalidArgumentException::class
        );

        $response = Http::asForm()->post(env('SSO_URL') . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => env('PASSPORT_CLIENT_ID'),
            'client_secret' => env('PASSPORT_CLIENT_SECRET'),
            'redirect_uri' => env('PASSPORT_CLIENT_CALLBACK_PATH'),
            'code' => $request->code,
        ]);

        $tokenData = $response->json();

        if (!isset($tokenData['access_token'])) {
            return response()->json([
                'error' => 'Failed to get access token',
                'details' => $tokenData,
            ], 400);
        }

        $userResponse = Http::withToken($tokenData['access_token'])
            ->get(env('SSO_URL') . '/api/user');
        $ssoUser = $userResponse->json();

        DB::transaction(function () use ($ssoUser) {
            $user = User::updateOrCreate(
                ['sso_user_id' => $ssoUser['id']],
                [
                    'name' => $ssoUser['name'],
                    'email' => $ssoUser['email'],
                ]
            );

            Auth::login($user);

            Monitoring::create([
                'user_id' => $user->id,
                'date' => now()->toDateString(),
                'time' => now()->toTimeString(),
                'activity' => 'login',
            ]);
        });

        $ssoUser = $userResponse->json();

        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:5173'), '/');

        return redirect()->away($frontendUrl . '/auth/callback?token=' . urlencode($tokenData['access_token']));
    }
}