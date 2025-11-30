<?php

namespace App\Filament\Widgets;

use App\Models\Celebration;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class AdminCelebrationsPerMonthChart extends ChartWidget
{
    protected static ?string $heading = 'Celebraciones por mes';

    protected function getData(): array
    {
        $months = collect(range(11, 0))->map(function ($i) {
            return Carbon::now()->subMonths($i)->format('Y-m');
        });

        $values = $months->map(function ($month) {
            return Celebration::whereYear('created_at', substr($month, 0, 4))
                ->whereMonth('created_at', substr($month, 5, 2))
                ->count();
        });

        return [
            'labels' => $months->map(fn ($m) => Carbon::parse($m)->isoFormat('MMM YY')),
            'datasets' => [
                [
                    'label' => 'Celebraciones',
                    'data' => $values,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
