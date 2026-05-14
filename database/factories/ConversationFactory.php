<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Gym;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gym_id' => Gym::factory(),
            'super_admin_id' => User::factory()->state(['is_super_admin' => true]),
            'title' => 'Support '.$this->faker->company(),
        ];
    }

    public function withoutSuperAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'super_admin_id' => null,
        ]);
    }
}
