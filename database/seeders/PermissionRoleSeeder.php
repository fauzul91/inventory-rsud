<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Super Admin
            'view_accounts',
            'edit_accounts',
            'assign_role',
            'view_log_activity',
            'view_profile',
            'edit_profile',

            // Admin Gudang Umum
            'view_dashboard',
            'view_penerimaan',
            'view_history_penerimaan',
            'download_bast',
            'upload_bast',
            'view_history_bast',
            'edit_pegawai_by_role',

            // Tim PPK
            'create_penerimaan',
            'delete_penerimaan_barang',

            // Tim Teknis
            'update_barang_layak',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [
            'Super Admin' => [
                'view_accounts',
                'edit_accounts',
                'assign_role',
                'view_log_activity',
                'view_profile',
                'edit_profile',
            ],

            'Admin Gudang Umum' => [
                'view_dashboard',
                'view_penerimaan',
                'view_history_penerimaan',
                'download_bast',
                'upload_bast',
                'view_history_bast',
                'view_profile',
                'edit_profile',
                'edit_pegawai_by_role',
            ],

            'Tim PPK' => [
                'create_penerimaan',
                'view_penerimaan',
                'view_history_penerimaan',
                'delete_penerimaan_barang',
                'view_profile',
                'edit_profile',
                'edit_pegawai_by_role',
            ],

            'Tim Teknis' => [
                'view_penerimaan',
                'view_history_penerimaan',
                'update_barang_layak',
                'view_profile',
                'edit_profile',
                'edit_pegawai_by_role',
            ],

            'Penanggung Jawab' => [
                'view_profile',
                'edit_profile',
                'edit_pegawai_by_role',
            ],

            'Instalasi' => [
                'view_profile',
                'edit_profile',
                'edit_pegawai_by_role',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }

        echo "Seeder roles & permissions selesai.\n";
    }
}
