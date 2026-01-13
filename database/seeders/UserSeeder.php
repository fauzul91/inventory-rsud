<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'SUPER_ADMIN' => 'super-admin',
            'ADMIN_GUDANG' => 'admin-gudang-umum',
            'TEKNIS' => 'tim-teknis',
            'PPK' => 'tim-ppk',
            'INSTALASI' => 'instalasi',
            'PENANGGUNG_JAWAB' => 'penanggung-jawab',
        ];

        foreach ($roles as $roleName) {
            Role::updateOrCreate(['name' => $roleName]);
        }

        $users = [
            [
                'sso_user_id' => 1,
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'role' => $roles['SUPER_ADMIN']
            ],
            [
                'sso_user_id' => 2,
                'name' => 'Admin Gudang',
                'email' => 'admingudang@example.com',
                'role' => $roles['ADMIN_GUDANG']
            ],
            [
                'sso_user_id' => 3,
                'name' => 'Tim Teknis',
                'email' => 'timteknis@example.com',
                'role' => $roles['TEKNIS']
            ],
            [
                'sso_user_id' => 4,
                'name' => 'Tim PPK',
                'email' => 'timppk@example.com',
                'role' => $roles['PPK']
            ],
            [
                'sso_user_id' => 5,
                'name' => 'Penanggung Jawab',
                'email' => 'penanggungjawab@example.com',
                'role' => $roles['PENANGGUNG_JAWAB']
            ],
            [
                'sso_user_id' => 6,
                'name' => 'Instalasi',
                'email' => 'instalasi@example.com',
                'role' => $roles['INSTALASI']
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'sso_user_id' => $userData['sso_user_id'],
                    'name' => $userData['name'],
                    'email_verified_at' => now(),
                    'remember_token' => Str::random(10),
                ]
            );

            if (!empty($userData['role'])) {
                $user->syncRoles($userData['role']);
            }
        }
    }
}
