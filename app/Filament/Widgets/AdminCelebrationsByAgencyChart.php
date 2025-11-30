<?php

namespace App\Filament\Widgets;

use App\Models\Agency;
use Filament\Widgets\ChartWidget;

class AdminCelebrationsByAgencyChart extends ChartWidget
{
    protected static ?string $heading = 'Celebraciones por agencia (Top 10)';

    protected function getData(): array
    {
        $data = Agency::withCount('celebrations')
            ->orderByDesc('celebrations_count')
            ->limit(10)
            ->get();

        return [
            'labels' => $data->pluck('name'),
            'datasets' => [
                [
                    'label' => 'Celebraciones',
                    'data' => $data->pluck('celebrations_count'),
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
