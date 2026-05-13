<?php

namespace Database\Seeders;

use App\Enums\CheckInType;
use App\Enums\PaymentStatus;
use App\Enums\PlanBillingPeriod;
use App\Models\CheckIn;
use App\Models\Gym;
use App\Models\Member;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Super Administrateur
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gym.com',
            'password' => Hash::make('password'),
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ]);

        // Gérants de Salles (Tenants)
        $gerant1 = User::factory()->create([
            'name' => 'Gérant Fitness Hub',
            'email' => 'admin@fitnesshub.com',
            'password' => Hash::make('password'),
            'is_super_admin' => false,
            'email_verified_at' => now(),
        ]);

        $gerant2 = User::factory()->create([
            'name' => 'Gérant Iron Paradise',
            'email' => 'admin@ironparadise.com',
            'password' => Hash::make('password'),
            'is_super_admin' => false,
            'email_verified_at' => now(),
        ]);

        $gyms = [
            Gym::create([
                'name' => 'Fitness Hub Dakar',
                'slug' => 'fitness-hub-dakar',
                'owner_id' => $gerant1->id,
                'plan_saas' => 'pro',
                'is_active' => true,
                'expires_at' => now()->addYear(),
            ]),
            Gym::create([
                'name' => 'Iron Paradise Abidjan',
                'slug' => 'iron-paradise-abidjan',
                'owner_id' => $gerant2->id,
                'plan_saas' => 'basic',
                'is_active' => true,
                'expires_at' => now()->addMonth(),
            ]),
        ];

        foreach ($gyms as $gym) {
            // 1. Forfaits types salle : mensuel, trimestriel, annuel
            $plans = [
                Plan::factory()->monthly()->create(['gym_id' => $gym->id]),
                Plan::factory()->create([
                    'gym_id' => $gym->id,
                    'name' => 'Trimestriel',
                    'price' => 40000,
                    'duration_days' => 90,
                    'billing_period' => PlanBillingPeriod::Quarterly,
                ]),
                Plan::factory()->annual()->create(['gym_id' => $gym->id]),
            ];

            // 2. Création de membres avec des abonnements
            $createSubscriber = function ($state) use ($plans, $gym) {
                $member = Member::factory()->create(['gym_id' => $gym->id]);

                $sub = Subscription::factory()->{$state}()->create([
                    'gym_id' => $gym->id,
                    'member_id' => $member->id,
                    'plan_id' => collect($plans)->random()->id,
                ]);

                Payment::factory()->create([
                    'gym_id' => $gym->id,
                    'member_id' => $member->id,
                    'subscription_id' => $sub->id,
                    'amount' => $sub->plan->price,
                    'status' => PaymentStatus::Completed,
                    'paid_at' => Carbon::parse($sub->start_date)->addHours(rand(1, 48)),
                ]);

                return $sub;
            };

            for ($i = 0; $i < 15; $i++) {
                $createSubscriber('active');
            }
            for ($i = 0; $i < 3; $i++) {
                $createSubscriber('expiringSoon');
            }
            for ($i = 0; $i < 5; $i++) {
                $createSubscriber('expired');
            }

            // 3. Création de membres "Walk-In"
            $walkIns = Member::factory()->walkIn()->count(5)->create(['gym_id' => $gym->id]);

            // 4. Générer des pointages et paiements
            foreach (range(1, 15) as $daysAgo) {
                $date = Carbon::today()->subDays(15 - $daysAgo);

                $dailySubs = Subscription::where('gym_id', $gym->id)->where('status', 'active')->inRandomOrder()->take(rand(2, 5))->get();
                foreach ($dailySubs as $sub) {
                    CheckIn::factory()->create([
                        'gym_id' => $gym->id,
                        'member_id' => $sub->member_id,
                        'subscription_id' => $sub->id,
                        'type' => CheckInType::Subscription,
                        'checked_in_at' => (clone $date)->addHours(rand(7, 21))->addMinutes(rand(0, 59)),
                    ]);
                }

                $dailyWalkIns = $walkIns->random(rand(1, 3));
                foreach ($dailyWalkIns as $walkIn) {
                    $checkInTime = (clone $date)->addHours(rand(7, 21))->addMinutes(rand(0, 59));

                    CheckIn::factory()->create([
                        'gym_id' => $gym->id,
                        'member_id' => $walkIn->id,
                        'subscription_id' => null,
                        'type' => CheckInType::WalkIn,
                        'checked_in_at' => $checkInTime,
                    ]);

                    Payment::factory()->create([
                        'gym_id' => $gym->id,
                        'member_id' => $walkIn->id,
                        'subscription_id' => null,
                        'amount' => rand(2, 5) * 1000,
                        'status' => PaymentStatus::Completed,
                        'paid_at' => $checkInTime,
                    ]);
                }
            }
        }
    }
}
