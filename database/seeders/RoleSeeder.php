<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'Admin Gudang Umum',
            'Tim Teknis',
            'Tim PPK',
            'Super Admin',
            'Penanggung Jawab',
            'Instalasi',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        echo "Seeder roles selesai.\n";
    }
}
