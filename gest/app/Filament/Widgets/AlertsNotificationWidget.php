<?php

namespace App\Filament\Widgets;

use App\Models\Alert;
use App\Support\CurrentGymResolver;
use Filament\Widgets\Widget;
use Livewire\Attributes\Computed;

class AlertsNotificationWidget extends Widget
{
    protected string $view = 'filament.widgets.alerts-notification-widget';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    #[Computed]
    public function unreadAlertsCount(): int
    {
        // Only show alerts to regular admins, not super-admins
        if (auth()->user()?->is_super_admin) {
            return 0;
        }

        $gymId = app(CurrentGymResolver::class)->resolve()?->id;

        if (! $gymId) {
            return 0;
        }

        return Alert::query()
            ->where('gym_id', $gymId)
            ->where('is_read', false)
            ->count();
    }

    #[Computed]
    public function recentAlerts()
    {
        // Only show alerts to regular admins, not super-admins
        if (auth()->user()?->is_super_admin) {
            return collect();
        }

        $gymId = app(CurrentGymResolver::class)->resolve()?->id;

        if (! $gymId) {
            return collect();
        }

        return Alert::query()
            ->where('gym_id', $gymId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function markAsRead(int $alertId): void
    {
        // Only allow regular admins to mark alerts as read, not super-admins
        if (auth()->user()?->is_super_admin) {
            return;
        }

        $alert = Alert::find($alertId);

        if ($alert) {
            $alert->markAsRead();
        }
    }

    public function markAllAsRead(): void
    {
        // Only allow regular admins to mark alerts as read, not super-admins
        if (auth()->user()?->is_super_admin) {
            return;
        }

        $gymId = app(CurrentGymResolver::class)->resolve()?->id;

        if (! $gymId) {
            return;
        }

        Alert::query()
            ->where('gym_id', $gymId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }
}
