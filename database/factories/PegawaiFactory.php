<?php

namespace Database\Factories;

use App\Models\Jabatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pegawai>
 */
class PegawaiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'nip' => $this->faker->unique()->numerify('######'),
            'phone' => $this->faker->phoneNumber(),
            'jabatan_id' => Jabatan::factory(),
            'status' => 'active',
        ];
    }
}
