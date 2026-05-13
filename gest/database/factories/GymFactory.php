<?php

namespace Database\Factories;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Gym>
 */
class GymFactory extends Factory
{
    protected $model = Gym::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->company().' Gym';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'owner_id' => User::factory(),
            'plan_saas' => fake()->randomElement(['basic', 'pro']),
            'is_active' => true,
            'expires_at' => now()->addMonth(),
            'walk_in_price' => fake()->randomFloat(2, 5, 50),
        ];
    }
}
