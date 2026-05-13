<?php

namespace Tests\Feature;

use App\Models\Gym;
use App\Models\User;
use App\Notifications\SaasRenewalReminderNotification;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SaasSuperAdminExperienceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_super_admin')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('gyms', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('plan_saas')->default('basic');
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->float('walk_in_price')->default(0);
            $table->timestamps();
        });
    }

    public function test_super_admin_can_trigger_saas_renewal_reminder_notification(): void
    {
        Notification::fake();

        $owner = User::factory()->create([
            'email' => 'owner@example.com',
        ]);

        $gym = Gym::query()->create([
            'name' => 'Fitness Hub Dakar',
            'slug' => 'fitness-hub-dakar',
            'owner_id' => $owner->id,
            'plan_saas' => 'basic',
            'is_active' => true,
            'expires_at' => now()->addDays(3),
            'walk_in_price' => 2500,
        ]);

        $this->assertTrue($gym->needsSaasReminder());

        $owner->notify(new SaasRenewalReminderNotification($gym));

        Notification::assertSentTo($owner, SaasRenewalReminderNotification::class);
    }

    public function test_landing_page_presents_saas_offers_on_arrival(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeText('Offres SaaS');
        $response->assertSeeText('Trial');
        $response->assertSeeText('Basic');
        $response->assertSeeText('Pro');
        $response->assertSeeText('Relances d’abonnement');
    }
}
