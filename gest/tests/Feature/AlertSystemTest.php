<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Gym;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\AlertService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AlertSystemTest extends TestCase
{
    private AlertService $alertService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->alertService = new AlertService;
    }

    #[Test]
    public function it_can_create_subscription_expiry_alert(): void
    {
        $gym = Gym::factory()->create();
        $member = Member::factory()->create(['gym_id' => $gym->id]);
        $plan = Plan::factory()->create(['gym_id' => $gym->id]);

        $subscription = Subscription::factory()->create([
            'gym_id' => $gym->id,
            'member_id' => $member->id,
            'plan_id' => $plan->id,
            'end_date' => now()->addDays(3),
        ]);

        $alert = $this->alertService->createSubscriptionExpiryAlert($subscription);

        $this->assertDatabaseHas('alerts', [
            'id' => $alert->id,
            'type' => Alert::TYPE_SUBSCRIPTION_EXPIRY,
            'gym_id' => $gym->id,
        ]);
    }

    #[Test]
    public function it_can_create_low_sessions_alert(): void
    {
        $gym = Gym::factory()->create();
        $member = Member::factory()->create(['gym_id' => $gym->id]);
        $plan = Plan::factory()->create(['gym_id' => $gym->id]);

        $subscription = Subscription::factory()->create([
            'gym_id' => $gym->id,
            'member_id' => $member->id,
            'plan_id' => $plan->id,
            'sessions_remaining' => 2,
        ]);

        $alert = $this->alertService->createLowSessionsAlert($subscription);

        $this->assertDatabaseHas('alerts', [
            'id' => $alert->id,
            'type' => Alert::TYPE_LOW_SESSIONS,
            'gym_id' => $gym->id,
        ]);
    }

    #[Test]
    public function it_can_mark_alert_as_read(): void
    {
        $alert = Alert::factory()->create(['is_read' => false]);

        $alert->markAsRead();

        $this->assertTrue($alert->fresh()->is_read);
    }

    #[Test]
    public function it_can_get_unread_alerts(): void
    {
        Alert::factory()->count(3)->create(['is_read' => false]);
        Alert::factory()->count(2)->create(['is_read' => true]);

        $unreadAlerts = Alert::unread()->get();

        $this->assertCount(3, $unreadAlerts);
    }

    #[Test]
    public function it_generates_payment_due_alert(): void
    {
        $gym = Gym::factory()->create();

        $alert = $this->alertService->createPaymentDueAlert($gym);

        $this->assertEquals(Alert::TYPE_PAYMENT_DUE, $alert->type);
        $this->assertEquals($gym->id, $alert->gym_id);
    }

    #[Test]
    public function it_generates_system_notification_alert(): void
    {
        $gym = Gym::factory()->create();

        $alert = $this->alertService->createSystemNotificationAlert(
            'Test Title',
            'Test Content',
            $gym
        );

        $this->assertEquals(Alert::TYPE_SYSTEM_NOTIFICATION, $alert->type);
        $this->assertEquals('Test Title', $alert->title);
    }
}
