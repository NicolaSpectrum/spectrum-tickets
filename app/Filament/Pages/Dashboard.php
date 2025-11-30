<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;
use App\Filament\Widgets\StatsOverviewAdmin;
use App\Filament\Widgets\OrganizerStatsOverview;
use App\Filament\Widgets\VerifierStatsOverview;
use App\Filament\Widgets\AdminCelebrationsStatusPieChart;
use App\Filament\Widgets\AdminCelebrationsPerMonthChart;
use App\Filament\Widgets\AdminCelebrationsByAgencyChart;
use App\Filament\Widgets\OrganizerCelebrationsPerMonthChart;
use App\Filament\Widgets\OrganizerTicketsStatusPieChart;
use App\Filament\Widgets\OrganizerCelebrationCheckinBarChart;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    public function getWidgets(): array
    {
        $user = Auth::user();

        return match (true) {
            $user->hasRole('admin') => [
                StatsOverviewAdmin::class,
                AdminCelebrationsPerMonthChart::class,
                AdminCelebrationsByAgencyChart::class,
                AdminCelebrationsStatusPieChart::class,
            ],

            $user->hasRole('organizer') => [
                OrganizerStatsOverview::class,
                OrganizerCelebrationCheckinBarChart::class,
                OrganizerCelebrationsPerMonthChart::class,
                OrganizerTicketsStatusPieChart::class,
            ],

            $user->hasRole('verifier') => [
                VerifierStatsOverview::class,
            ],

            default => [],
        };
    }

    public function getColumns(): array
    {
        return [
            'default' => 1,  // móvil
            'sm' => 1,
            'md' => 2,       // tablet
            'lg' => 2,
            'xlg' => 2, 
        ];
    }

    public static function getWidgetLayout(): array
    {
        $user = Auth::user();

        /* ---------- ORGANIZER ---------- */
        if ($user->hasRole('organizer')) {
            return [
                OrganizerStatsOverview::make()
                    ->columnSpan([
                        'default' => 1,
                        'md' => 1,
                        'lg' => 2,
                    ]),

                OrganizerCelebrationCheckinBarChart::make()
                    ->columnSpan([
                        'default' => 1,
                        'md' => 2,
                    ]),

                OrganizerCelebrationsPerMonthChart::make()
                    ->columnSpan([
                        'default' => 1,
                        'md' => 2,
                    ]),

                OrganizerTicketsStatusPieChart::make()
                    ->columnSpan([
                        'default' => 1,
                        'md' => 2,
                    ]),
            ];
        }

        /* ---------- ADMIN ---------- */
        if ($user->hasRole('admin')) {
            return [
                StatsOverviewAdmin::make()
                    ->columnSpan([
                        'default' => 1,
                        'md' => 3,
                    ]),

                AdminCelebrationsPerMonthChart::make()
                    ->columnSpan([
                        'default' => 1,
                        'md' => 2,
                    ]),

                AdminCelebrationsByAgencyChart::make()
                    ->columnSpan([
                        'default' => 1,
                        'md' => 1,
                    ]),

                AdminCelebrationsStatusPieChart::make()
                    ->columnSpan([
                        'default' => 1,
                        'md' => 1,
                    ]),
            ];
        }

        /* ---------- VERIFIER ---------- */
        if ($user->hasRole('verifier')) {
            return [
                VerifierStatsOverview::make()
                    ->columnSpan([
                        'default' => 1,
                        'md' => 2,
                    ]),
            ];
        }

        return [];
    }
}
