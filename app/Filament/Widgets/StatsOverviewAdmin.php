<?php

namespace App\Filament\Widgets;

use App\Models\Agency;
use App\Models\User;
use App\Models\Celebration;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverviewAdmin extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();

        return [
            Stat::make('Agencias Registradas', Agency::count())
                ->description('Total de agencias activas en el sistema')
                ->descriptionIcon('heroicon-o-building-office')
                ->color('primary'),

            Stat::make('Usuarios Totales', User::count())
                ->description('Usuarios pertenecientes a todas las agencias')
                ->descriptionIcon('heroicon-o-users')
                ->color('success'),

            Stat::make('Celebraciones Registradas', Celebration::count())
                ->description('Eventos creados en todas las agencias')
                ->descriptionIcon('heroicon-o-gift')
                ->color('warning'),

            Stat::make( 'Celebraciones Este Mes',
                Celebration::where('created_at', '>=', $monthStart)->count())
                ->description('Actividad reciente')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),
        ];
    }
}
