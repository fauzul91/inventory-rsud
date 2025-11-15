<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pegawais = [
            ['name' => 'Budi Santoso', 'nip' => '19890101', 'phone' => '081234567890', 'status' => 'active', 'jabatan_id' => 1],
            ['name' => 'Siti Aminah', 'nip' => '19890202', 'phone' => '081234567891', 'status' => 'active', 'jabatan_id' => 2],
            ['name' => 'Andi Wijaya', 'nip' => '19890303', 'phone' => '081234567892', 'status' => 'active', 'jabatan_id' => 3],
            ['name' => 'Rina Lestari', 'nip' => '19890404', 'phone' => '081234567893', 'status' => 'active', 'jabatan_id' => 1],
            ['name' => 'Agus Pratama', 'nip' => '19890505', 'phone' => '081234567894', 'status' => 'inactive', 'jabatan_id' => 2],
            ['name' => 'Dewi Anggraini', 'nip' => '19890606', 'phone' => '081234567895', 'status' => 'active', 'jabatan_id' => 3],
            ['name' => 'Tono Setiawan', 'nip' => '19890707', 'phone' => '081234567896', 'status' => 'active', 'jabatan_id' => 1],
            ['name' => 'Mira Safitri', 'nip' => '19890808', 'phone' => '081234567897', 'status' => 'active', 'jabatan_id' => 2],
            ['name' => 'Hadi Pranoto', 'nip' => '19890909', 'phone' => '081234567898', 'status' => 'inactive', 'jabatan_id' => 3],
            ['name' => 'Lina Marlina', 'nip' => '19891010', 'phone' => '081234567899', 'status' => 'active', 'jabatan_id' => 1],
        ];

        foreach ($pegawais as $pegawai) {
            Pegawai::firstOrCreate([
                'nip' => $pegawai['nip'],
            ], $pegawai);
        }

        echo "Seeder pegawai selesai.\n";
    }
}
