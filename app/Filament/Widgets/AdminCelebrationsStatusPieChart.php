<?php

namespace App\Filament\Widgets;

use App\Models\Celebration;
use Filament\Widgets\ChartWidget;

class AdminCelebrationsStatusPieChart extends ChartWidget
{
    protected static ?string $heading = 'Estados de celebraciones';

    protected function getData(): array
    {
        $statuses = [
            'draft',
            'pending_approval',
            'approved',
            'rejected',
            'completed',
            'cancelled',
        ];

        $counts = collect($statuses)->map(fn ($status) => 
            Celebration::where('status', $status)->count()
        );

        return [
            'labels' => $statuses,
            'datasets' => [
                [
                    'data' => $counts,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
