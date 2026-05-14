<?php

namespace App\Filament\Widgets;

use App\Enums\CheckInType;
use App\Models\CheckIn;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class WalkInsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Fréquentation — séances uniques';

    protected ?string $description = 'Nombre d’entrées « séance unique » par jour (14 derniers jours).';

    protected function getData(): array
    {
        $start = Carbon::today()->subDays(13)->startOfDay();
        $labels = [];
        $counts = [];

        for ($i = 0; $i < 14; $i++) {
            $day = (clone $start)->addDays($i);
            $labels[] = $day->format('d/m');
            $counts[] = CheckIn::query()
                ->where('type', CheckInType::WalkIn)
                ->whereDate('checked_in_at', $day->toDateString())
                ->count();
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Séances uniques',
                    'data' => $counts,
                    'fill' => 'start',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
