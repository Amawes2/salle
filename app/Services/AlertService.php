<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Gym;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AlertService
{
    /**
     * Create a new subscription expiry alert.
     */
    public function createSubscriptionExpiryAlert(Subscription $subscription): Alert
    {
        $member = $subscription->member;
        $gym = $subscription->gym;
        $daysLeft = now()->diffInDays($subscription->end_date, false);

        $title = 'Abonnement expirant';
        $content = "L'abonnement de {$member->name} expire ";

        if ($daysLeft < 0) {
            $content .= 'il y a '.abs($daysLeft).' jour(s).';
        } elseif ($daysLeft === 0) {
            $content .= "aujourd'hui.";
        } else {
            $content .= "dans {$daysLeft} jour(s).";
        }

        return Alert::create([
            'gym_id' => $gym->id,
            'type' => Alert::TYPE_SUBSCRIPTION_EXPIRY,
            'title' => $title,
            'content' => $content,
            'data' => [
                'subscription_id' => $subscription->id,
                'member_id' => $member->id,
                'days_left' => $daysLeft,
            ],
        ]);
    }

    /**
     * Create a new low sessions alert.
     */
    public function createLowSessionsAlert(Subscription $subscription): Alert
    {
        $member = $subscription->member;
        $gym = $subscription->gym;

        $title = 'Séances restantes faibles';
        $content = "{$member->name} n'a plus que {$subscription->sessions_remaining} séance(s) restante(s).";

        return Alert::create([
            'gym_id' => $gym->id,
            'type' => Alert::TYPE_LOW_SESSIONS,
            'title' => $title,
            'content' => $content,
            'data' => [
                'subscription_id' => $subscription->id,
                'member_id' => $member->id,
                'sessions_remaining' => $subscription->sessions_remaining,
            ],
        ]);
    }

    /**
     * Create a new payment due alert.
     */
    public function createPaymentDueAlert(Gym $gym, ?string $message = null): Alert
    {
        $title = 'Paiement SaaS à effectuer';
        $content = $message ?? "Le paiement pour l'abonnement SaaS de {$gym->name} est dû.";

        return Alert::create([
            'gym_id' => $gym->id,
            'type' => Alert::TYPE_PAYMENT_DUE,
            'title' => $title,
            'content' => $content,
            'data' => [
                'expires_at' => $gym->expires_at?->toDateTimeString(),
                'days_left' => $gym->getDaysUntilExpiry(),
            ],
        ]);
    }

    /**
     * Create a new system notification alert.
     */
    public function createSystemNotificationAlert(string $title, string $content, ?Gym $gym = null, array $data = []): Alert
    {
        return Alert::create([
            'gym_id' => $gym?->id,
            'type' => Alert::TYPE_SYSTEM_NOTIFICATION,
            'title' => $title,
            'content' => $content,
            'data' => $data,
        ]);
    }

    /**
     * Create a new message alert.
     */
    public function createNewMessageAlert(
        User $recipient,
        User $sender,
        string $messagePreview,
        int $conversationId,
        ?int $gymId = null
    ): Alert {
        $title = 'Nouveau message';
        $content = "{$sender->name}: {$messagePreview}";

        return Alert::create([
            'user_id' => $recipient->id,
            'gym_id' => $gymId,
            'type' => Alert::TYPE_NEW_MESSAGE,
            'title' => $title,
            'content' => $content,
            'data' => [
                'sender_id' => $sender->id,
                'sender_name' => $sender->name,
                'conversation_id' => $conversationId,
            ],
        ]);
    }

    /**
     * Check for subscriptions that are expiring soon and create alerts.
     */
    public function checkForExpiringSubscriptions(?Gym $gym = null): void
    {
        $query = Subscription::query()
            ->where('status', 'active')
            ->whereBetween('end_date', [
                now()->toDateString(),
                now()->addDays(7)->toDateString(),
            ]);

        if ($gym) {
            $query->where('gym_id', $gym->id);
        }

        $expiringSubscriptions = $query->get();

        foreach ($expiringSubscriptions as $subscription) {
            /** @var Subscription $subscription */
            try {
                $this->createSubscriptionExpiryAlert($subscription);
            } catch (\Exception $e) {
                Log::error('Failed to create subscription expiry alert', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check for subscriptions with low session counts and create alerts.
     */
    public function checkForLowSessions(?Gym $gym = null, int $threshold = 3): void
    {
        $query = Subscription::query()
            ->where('status', 'active')
            ->whereNotNull('sessions_remaining')
            ->where('sessions_remaining', '<=', $threshold)
            ->where('sessions_remaining', '>', 0);

        if ($gym) {
            $query->where('gym_id', $gym->id);
        }

        $lowSessionSubscriptions = $query->get();

        foreach ($lowSessionSubscriptions as $subscription) {
            /** @var Subscription $subscription */
            try {
                $this->createLowSessionsAlert($subscription);
            } catch (\Exception $e) {
                Log::error('Failed to create low sessions alert', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Check for gyms with expiring SaaS subscriptions and create alerts.
     */
    public function checkForExpiringSaasSubscriptions(): void
    {
        $expiringGyms = Gym::query()
            ->where('is_active', true)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [
                now()->toDateString(),
                now()->addDays(7)->toDateString(),
            ])
            ->get();

        foreach ($expiringGyms as $gym) {
            /** @var Gym $gym */
            try {
                $daysLeft = now()->diffInDays($gym->expires_at, false);
                $message = "L'abonnement SaaS de {$gym->name} expire ";

                if ($daysLeft < 0) {
                    $message .= 'il y a '.abs($daysLeft).' jour(s).';
                } elseif ($daysLeft === 0) {
                    $message .= "aujourd'hui.";
                } else {
                    $message .= "dans {$daysLeft} jour(s).";
                }

                $this->createPaymentDueAlert($gym, $message);
            } catch (\Exception $e) {
                Log::error('Failed to create payment due alert', [
                    'gym_id' => $gym->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
