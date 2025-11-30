<?php

namespace App\Filament\Widgets;

use App\Models\Celebration;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrganizerCelebrationsPerMonthChart extends ChartWidget
{
    protected static ?string $heading = 'Celebraciones por Mes';

    protected function getData(): array
    {
        $user = Auth::user();

        // Detectar driver (SQLite/MySQL)
        $driver = DB::getDriverName();
        $monthField = $driver === 'sqlite'
            ? "strftime('%m', created_at)"
            : "MONTH(created_at)";

        $stats = Celebration::select(
                DB::raw("COUNT(*) as total"),
                DB::raw("$monthField as month")
            )
            ->where('created_by', $user->id)  // organizer correcto
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = [];
        $data = [];

        foreach ($stats as $item) {
            $monthNumber = (int) $item->month;

            $labels[] = Carbon::create()->month($monthNumber)->format('F');
            $data[] = $item->total;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Celebraciones creadas',
                    'data' => $data,
                    'hoverOffset' => 10,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
