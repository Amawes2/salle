<?php

namespace App\Filament\SuperAdmin\Widgets;

use App\Models\Gym;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Support\Icons\Heroicon;

class SaasOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'SaaS — Chiffres clefs';

    protected function getStats(): array
    {
        // MRR approximated as payments in last 30 days
        $mrr = Payment::query()
            ->where('status', 'completed')
            ->whereBetween('paid_at', [now()->subDays(30), now()])
            ->sum('amount');

        $newGymsThisMonth = Gym::query()
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        return [
            Stat::make('MRR', number_format((float) $mrr, 0, ',', ' ').' FCFA')
                ->description('Revenu récurrent (30 jours)')
                ->descriptionIcon(Heroicon::OutlinedBanknotes)
                ->color('success'),

            Stat::make('Nouvelles salles', $newGymsThisMonth)
                ->description('Inscrites ce mois')
                ->descriptionIcon(Heroicon::OutlinedBuildingLibrary)
                ->color('primary'),
        ];
    }
}
