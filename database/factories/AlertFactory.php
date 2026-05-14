<?php

namespace Database\Factories;

use App\Models\Alert;
use App\Models\Gym;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    protected $model = Alert::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gym_id' => Gym::factory(),
            'user_id' => null,
            'type' => $this->faker->randomElement([
                Alert::TYPE_SUBSCRIPTION_EXPIRY,
                Alert::TYPE_LOW_SESSIONS,
                Alert::TYPE_PAYMENT_DUE,
                Alert::TYPE_SYSTEM_NOTIFICATION,
                Alert::TYPE_NEW_MESSAGE,
            ]),
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'is_read' => false,
            'data' => [],
        ];
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
        ]);
    }

    public function subscriptionExpiry(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Alert::TYPE_SUBSCRIPTION_EXPIRY,
            'title' => 'Abonnement expirant',
            'content' => 'Un abonnement expire dans les 7 prochains jours',
        ]);
    }

    public function lowSessions(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Alert::TYPE_LOW_SESSIONS,
            'title' => 'Séances restantes faibles',
            'content' => 'Un membre n\'a plus que quelques séances restantes',
        ]);
    }

    public function paymentDue(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Alert::TYPE_PAYMENT_DUE,
            'title' => 'Paiement SaaS à effectuer',
            'content' => 'Le paiement pour l\'abonnement SaaS est dû',
        ]);
    }

    public function newMessage(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Alert::TYPE_NEW_MESSAGE,
            'title' => 'Nouveau message',
            'content' => 'Vous avez reçu un nouveau message',
            'user_id' => User::factory(),
        ]);
    }
}
