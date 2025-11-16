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
            ],
            [
                'name' => 'Admin Gudang',
                'sso_user_id' => 2,
                'email' => 'admingudang@example.com',
                'photo' => null,
            ],
            [
                'name' => 'Tim Teknis',
                'sso_user_id' => 3,
                'email' => 'timteknis@example.com',
                'photo' => null,
            ],
            [
                'name' => 'Tim PPK',
                'sso_user_id' => 4,
                'email' => 'timppk@example.com',
                'photo' => null,
            ],
            [
                'name' => 'Penanggung Jawab',
                'sso_user_id' => 5,
                'email' => 'penanggungjawab@example.com',
                'photo' => null,
            ],
            [
                'name' => 'Instalasi',
                'sso_user_id' => 6,
                'email' => 'instalasi@example.com',
                'photo' => null,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']], // unik key
                [
                    'name' => $user['name'],
                    'sso_user_id' => $user['sso_user_id'],
                    'email_verified_at' => now(),
                    'remember_token' => Str::random(10),
                    'photo' => $user['photo'],
                ]
            );
        }

        echo "Seeder user lokal selesai.\n";
    }
}
