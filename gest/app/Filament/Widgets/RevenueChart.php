<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Bilan financier (7 jours)';

    protected ?string $description = 'Revenus liés à un abonnement vs paiements sans abonnement (tickets / caisse).';

    protected function getData(): array
    {
        $labels = [];
        $subscriptionRevenue = [];
        $walkInRevenue = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $labels[] = $day->format('d/m');

            $subscriptionRevenue[] = (float) Payment::query()
                ->where('status', PaymentStatus::Completed)
                ->whereDate('paid_at', $day->toDateString())
                ->whereNotNull('subscription_id')
                ->sum('amount');

            $walkInRevenue[] = (float) Payment::query()
                ->where('status', PaymentStatus::Completed)
                ->whereDate('paid_at', $day->toDateString())
                ->whereNull('subscription_id')
                ->sum('amount');
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Revenus abonnements (paiement lié)',
                    'data' => $subscriptionRevenue,
                ],
                [
                    'label' => 'Revenus passages / tickets',
                    'data' => $walkInRevenue,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
