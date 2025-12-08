<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Penerimaan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PenerimaanFactory extends Factory
{
    protected $model = Penerimaan::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'no_surat' => strtoupper($this->faker->bothify('INV-####')),
            'deskripsi' => $this->faker->sentence(),
            'status' => 'pending',
        ];
    }
}
