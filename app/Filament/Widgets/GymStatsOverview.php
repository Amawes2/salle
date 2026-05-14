<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\CheckIn;
use App\Models\Payment;
use App\Models\Subscription;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class GymStatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;
    protected static ?int $sort = 2;

    protected ?string $heading = 'Aujourd’hui en un coup d’œil';

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $period = $this->filters['period'] ?? 'today';
        $startDate = match ($period) {
            'today' => today(),
            'week' => today()->startOfWeek(),
            'month' => today()->startOfMonth(),
            'year' => today()->startOfYear(),
            'all' => now()->subYears(10), // essentially all time
            default => today(),
        };

        $endDate = today()->endOfDay();

        $activeSubscriptionsCount = Subscription::query()
            ->active()
            ->pluck('member_id')
            ->unique()
            ->count();

        $todayCheckIns = CheckIn::query()
            ->whereBetween('checked_in_at', [$startDate, $endDate])
            ->count();

        $expiringSoonCount = Subscription::query()
            ->where('status', SubscriptionStatus::Active->value)
            ->whereBetween('end_date', [today()->toDateString(), today()->addDays(7)->toDateString()])
            ->count();

        $todayCash = Payment::query()
            ->where('status', PaymentStatus::Completed)
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->sum('amount');

        return [
            Stat::make('Abonnements actifs', $activeSubscriptionsCount)
                ->description('Accès valide')
                ->descriptionIcon(Heroicon::OutlinedUserGroup)
                ->color('success'),

            Stat::make('Entrées', $todayCheckIns)
                ->description(match($period) { 'week' => 'Sur 7 jours', 'month' => 'Sur le mois', 'year' => 'Sur l\'année', 'all' => 'Total', default => 'Passages enregistrés', })
                ->descriptionIcon(Heroicon::OutlinedArrowRightEndOnRectangle)
                ->color('primary'),

            Stat::make('Expirent bientôt', $expiringSoonCount)
                ->description('Dans les 7 prochains jours')
                ->descriptionIcon(Heroicon::OutlinedExclamationTriangle)
                ->color($expiringSoonCount > 0 ? 'warning' : 'gray'),

            Stat::make('Caisse', number_format((float) $todayCash, 0, ',', ' ').' FCFA')
                ->description(match($period) { 'week' => 'Encaissements sur 7 jours', 'month' => 'Sur le mois', 'year' => 'Sur l\'année', 'all' => 'Total', default => 'Encaissements du jour', })
                ->descriptionIcon(Heroicon::OutlinedBanknotes)
                ->color('success'),
        ];
    }
}
