<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Admin',
                'sso_user_id' => 1,
                'email' => 'superadmin@example.com',
                'photo' => null,
                'role' => 'Super Admin',
            ],
            [
                'name' => 'Admin Gudang',
                'sso_user_id' => 2,
                'email' => 'admingudang@example.com',
                'photo' => null,
                'role' => 'Admin Gudang Umum',
            ],
            [
                'name' => 'Tim Teknis',
                'sso_user_id' => 3,
                'email' => 'timteknis@example.com',
                'photo' => null,
                'role' => 'Tim Teknis',
            ],
            [
                'name' => 'Tim PPK',
                'sso_user_id' => 4,
                'email' => 'timppk@example.com',
                'photo' => null,
                'role' => 'Tim PPK',
            ],
            [
                'name' => 'Penanggung Jawab',
                'sso_user_id' => 5,
                'email' => 'penanggungjawab@example.com',
                'photo' => null,
                'role' => 'Penanggung Jawab',
            ],
            [
                'name' => 'Instalasi',
                'sso_user_id' => 6,
                'email' => 'instalasi@example.com',
                'photo' => null,
                'role' => 'Instalasi',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']], // unik key
                [
                    'name' => $userData['name'],
                    'sso_user_id' => $userData['sso_user_id'],
                    'email_verified_at' => now(),
                    'remember_token' => Str::random(10),
                    'photo' => $userData['photo'],
                ]
            );

            if (!empty($userData['role'])) {
                $user->assignRole($userData['role']);
            }
        }

        echo "Seeder user lokal selesai, role sudah diassign.\n";
    }
}
