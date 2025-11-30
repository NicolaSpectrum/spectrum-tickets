<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Celebration;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class OrganizerStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $agencyId = auth()->user()->agency_id;
        $monthStart = Carbon::now()->startOfMonth();

        $lastCelebration = Celebration::where('agency_id', $agencyId)->latest()->first();

        return [
            Stat::make(
                'Celebraciones de la Agencia',
                Celebration::where('agency_id', $agencyId)->count()
            )
                ->description('Total de celebraciones creadas')
                ->descriptionIcon('heroicon-o-cake')
                ->color('primary'),

            Stat::make(
                'Usuarios de la Agencia',
                User::where('agency_id', $agencyId)->count()
            )
                ->description('Miembros registrados en tu agencia')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('success'),

            Stat::make(
                'Celebraciones Activas Hoy',
                Celebration::where('agency_id', $agencyId)
                    ->whereDate('date', today())
                    ->count()
            )
                ->description('Eventos que suceden hoy')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('warning'),

            Stat::make('Última Celebración',$lastCelebration? $lastCelebration->created_at->format('d M Y'): '—')
                ->description('Fecha del último evento')
                ->descriptionIcon('heroicon-m-clock'),
        ];
    }
}
