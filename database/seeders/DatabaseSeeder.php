<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            JabatanSeeder::class,
            PegawaiSeeder::class,
            UserSeeder::class,
            StokSeeder::class,
            PenerimaanSeeder::class,
            PemesananSeeder::class
        ]);
    }
}
