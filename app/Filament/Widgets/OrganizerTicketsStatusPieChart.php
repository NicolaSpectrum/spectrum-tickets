<?php

namespace App\Filament\Widgets;

use App\Models\Celebration;
use App\Models\Registration;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;


class OrganizerTicketsStatusPieChart extends ChartWidget
{
    protected static ?string $heading = 'Tikets con Check-ins Vs sin Check-ins';
    protected static ?string $description = 'Tikets totales de la agencia que ya realizaron check-in y los no han realizado check-in';

    protected function getData(): array
    {
        $user = Auth::user();

        // Celebraciones de la agencia del organizador
        $celebrationIds = Celebration::where('agency_id', $user->agency_id)->pluck('id');

        $totalRegistrations = Registration::whereIn('celebration_id', $celebrationIds)->count();
        $checkedIn = Registration::whereIn('celebration_id', $celebrationIds)->where('checked_in', true)->count();

        return [
            'labels' => [
                'Tikets Sin Check-in',
                'Tikets Con Check-in',
            ],
            'datasets' => [
                [
                    'data' => [
                        $totalRegistrations-$checkedIn,
                        $checkedIn,
                    ],
                    'backgroundColor' => [
                        '#2563EB', // Azul – Registros Totales
                        '#16A34A', // Verde – Check-ins
                    ],
                    'hoverOffset' => 10,
                    'borderWidth' => 0,
                    
                ],
            ],
            'options' => [
                'scales' => [
                    'y' => [
                        'display' => false,
                        'grid' => ['display' => false],
                        'ticks' => ['display' => false],
                    ]
                ]
            ]
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

}
