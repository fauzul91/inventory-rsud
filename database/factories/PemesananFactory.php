<?php

namespace Database\Factories;

use App\Models\Pemesanan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PemesananFactory extends Factory
{
    protected $model = Pemesanan::class;

    public function definition(): array
    {
        return [
            'user_id'           => User::factory(),
            'nama_pj_instalasi' => $this->faker->name(),
            'ruangan'           => $this->faker->word(),
            'tanggal_pemesanan' => $this->faker->date(),
        ];
    }
}
