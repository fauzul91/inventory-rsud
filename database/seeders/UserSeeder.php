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
        // Contoh 5 user dummy
        $users = [
            [
                'name' => 'Admin Gudang',
                'sso_user_id' => 1,
                'email' => 'admin.gudang@example.com',
                'photo' => null,
            ],
            [
                'name' => 'Tim Teknis',
                'sso_user_id' => 2,
                'email' => 'tim.teknis@example.com',
                'photo' => null,
            ],
            [
                'name' => 'Tim PPK',
                'sso_user_id' => 3,
                'email' => 'tim.ppk@example.com',
                'photo' => null,
            ],
            [
                'name' => 'Penanggung Jawab',
                'sso_user_id' => 4,
                'email' => 'penanggung.jawab@example.com',
                'photo' => null,
            ],
            [
                'name' => 'Instalasi',
                'sso_user_id' => 5,
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
