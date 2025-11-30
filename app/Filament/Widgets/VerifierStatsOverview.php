<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Celebration;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Registration;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VerifierStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $agencyId = auth()->user()->agency_id;

        // Si no hay fecha, mostramos la última creada como referencia
        $nextCelebration = Celebration::where('agency_id', $agencyId)
            ->whereDate('created_at', '>=', Carbon::now()->startOfDay())
            ->orderBy('created_at')
            ->first();

        // Tickets que este verifier ha validado
        $validatedTotal = Registration::where('verified_by', $user->id)->count();
        
        // Validados hoy
        $validatedToday = Registration::where('verified_by', $user->id)
            ->whereDate('checked_in_at', now()->toDateString())
            ->count();
        
            // Pendientes asignadas a su agencia (si aplica)
        $pending = Registration::whereHas('celebration', function ($q) use ($user) {
            if ($user->agency_id) {
                $q->where('agency_id', $user->agency_id);
            }
        })->where('checked_in', false)->count();


        if (!$nextCelebration) {
            $nextCelebration = Celebration::where('agency_id', $agencyId)
                ->latest()
                ->first();
        }

        return [

            Stat::make('Verificados (hoy)', $validatedToday)
                ->description('Hoy')
                ->color('success'),

            Stat::make('Verificados (total)', $validatedTotal)
                ->description('Total por ti')
                ->color('primary'),

            Stat::make('Pendientes', $pending)
                ->description('Tickets no validados (tu agencia)')
                ->color('warning'),


            Stat::make(
                'Celebraciones Hoy',
                Celebration::where('agency_id', $agencyId)
                ->whereDate('date', today())
                ->count()
            )
                ->description('Eventos programados para hoy')
                ->descriptionIcon('heroicon-o-calendar')
                ->color('primary'),
        
            Stat::make(
                'Celebraciones de la Agencia',
                Celebration::where('agency_id', $agencyId)->count()
            )
                ->description('Eventos donde validas accesos')
                ->descriptionIcon('heroicon-m-sparkles'),

            Stat::make(
                'Usuarios de la Agencia',
                User::where('agency_id', $agencyId)->count()
            )
                ->description('Compañeros de trabajo')
                ->descriptionIcon('heroicon-m-users'),

            Stat::make(
                'Próxima Celebración',
                $nextCelebration
                    ? $nextCelebration->created_at->format('d M Y')
                    : '—'
            )
                ->description('Evento más cercano')
                ->descriptionIcon('heroicon-m-calendar'),
        ];
    }
}
