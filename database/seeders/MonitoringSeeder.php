<?php

namespace Database\Seeders;

use App\Models\Monitoring;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
class MonitoringSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $activities = [
            'melakukan login',
            'melakukan logout',
            'membuat penerimaan',
            'update penerimaan',
            'update kelayakan barang',
            'konfirmasi penerimaan',
            'mengupload BAST',
        ];

        for ($i = 0; $i < 20; $i++) {
            Monitoring::create([
                'user_id' => rand(1, 6),
                'time' => $faker->time('H:i:s'),
                'date' => $faker->date('Y-m-d'),
                'activity' => $activities[array_rand($activities)],
            ]);
        }
    }
}
