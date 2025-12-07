<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'sso_user_id' => $this->faker->unique()->randomNumber(5),
            'photo' => null,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }
}
