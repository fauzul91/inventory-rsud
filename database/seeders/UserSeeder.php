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
            'sso_user_id' => 1, // Cocok (Urutan 1 di SSO)
            'email' => 'bluepylox@gmail.com', // Samakan emailnya biar tidak bingung
            'photo' => null,
            'role' => 'super-admin',
        ],
        [
            'name' => 'Tim PPK',
            'sso_user_id' => 2, // ✅ UBAH JADI 2 (Sesuai urutan create di SSO)
            'photo' => null,
            'email' => 'timppk@gmail.com',
            'role' => 'tim-ppk',
        ],
        [
            'name' => 'Instalasi',
            'sso_user_id' => 3, // ✅ UBAH JADI 3
            'photo' => null,
            'email' => 'instalasi@gmail.com',
            'role' => 'instalasi',
        ],
        [
            'name' => 'Admin Gudang',
            'sso_user_id' => 4, // ✅ UBAH JADI 4
            'photo' => null,
            'email' => 'adminGudang@gmail.com',
            'role' => 'admin-gudang-umum',
        ],
        [
            'name' => 'Tim Teknis',
            'sso_user_id' => 5, // ✅ UBAH JADI 5
            'photo' => null,
            'email' => 'teknis@gmail.com',
            'role' => 'tim-teknis',
        ],
        [
            'name' => 'Penanggung Jawab',
            'sso_user_id' => 6, // ✅ UBAH JADI 6
            'photo' => null,
            'email' => 'penanggungJawab@gmail.com',
            'role' => 'penanggung-jawab',
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
