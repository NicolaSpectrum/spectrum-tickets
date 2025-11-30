<?php

namespace App\Filament\Widgets;

use App\Models\Celebration;
use App\Models\Registration;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class OrganizerCelebrationCheckinBarChart extends ChartWidget
{
    protected static ?string $heading = 'Registros por Celebración';

    protected function getData(): array
    {
        $user = Auth::user();

        // Celebraciones creadas por el organizer
        $celebrations = Celebration::where('created_by', $user->id)->get();

        $labels = [];
        $totals = [];
        $checkedIns = [];
        $pending = [];

        foreach ($celebrations as $celebration) {

            $total = Registration::where('celebration_id', $celebration->id)->count();
            $checked = Registration::where('celebration_id', $celebration->id)
                ->where('checked_in', true)
                ->count();
            $notChecked = $total - $checked;

            $labels[] = $celebration->name;  // FIX
            $totals[] = $total;
            $checkedIns[] = $checked;
            $pending[] = $notChecked;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total tikets',
                    'data' => $totals,
                    'backgroundColor' => '#9E9E9E',
                    'borderWidth' => 0,
                    
                ],
                [
                    'label' => 'Check-in',
                    'data' => $checkedIns,
                    'backgroundColor' => '#4CAF50',
                    'borderWidth' => 0,
                    
                ],
                [
                    'label' => 'Pendientes',
                    'data' => $pending,
                    'backgroundColor' => '#F44336',
                    'borderWidth' => 0,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
