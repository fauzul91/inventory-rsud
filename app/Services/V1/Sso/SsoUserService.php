<?php

namespace App\Services\V1\Sso;

use App\Models\User;
use Spatie\Permission\Models\Role;

class SsoUserService
{
    public function syncUser(array $ssoUser): array
    {
        $roleName = $ssoUser['roles'][0] ?? 'guest';

        $user = User::updateOrCreate(
            ['sso_user_id' => $ssoUser['id']],
            [
                'email' => $ssoUser['email'],
                'name' => $ssoUser['name'],
            ]
        );

        Role::firstOrCreate(['name' => $roleName]);
        $user->syncRoles($roleName);

        $user->tokens()->delete();
        $token = $user->createToken('sso-login')->plainTextToken;

        return [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $roleName,
            ]
        ];
    }
}
