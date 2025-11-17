<?php

namespace App\Filament\Widgets;

use App\Models\Registration;
use App\Models\Tournament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Count active tournaments
        $activeTournaments = Tournament::whereIn('status', ['published', 'registration_open', 'running'])
            ->count();

        // Count all registrations
        $totalRegistrations = Registration::whereIn('status', ['registered', 'checked_in'])->count();
        
        // Count checked in
        $checkedIn = Registration::where('status', 'checked_in')->count();
        
        // Count waiting list
        $waitingList = Registration::where('status', 'waiting')->count();

        return [
            Stat::make('Active Tournaments', $activeTournaments)
                ->description('Currently running or open')
                ->descriptionIcon('heroicon-o-trophy')
                ->color('success'),

            Stat::make('Total Registrations', $totalRegistrations)
                ->description('Registered + Checked In')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('primary')
                ->chart($this->getRegistrationTrend()),

            Stat::make('Checked In', $checkedIn)
                ->description('Players present')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('info'),

            Stat::make('Waiting List', $waitingList)
                ->description('People waiting')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),
        ];
    }

    private function getRegistrationTrend(): array
    {
        // Get last 7 days registration counts
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $count = Registration::whereDate('created_at', $date)->count();
            $trend[] = $count;
        }
        return $trend;
    }
}
