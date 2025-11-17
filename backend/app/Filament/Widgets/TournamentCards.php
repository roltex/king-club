<?php

namespace App\Filament\Widgets;

use App\Models\Tournament;
use App\Models\CashGame;
use Filament\Widgets\Widget;
use Illuminate\Support\HtmlString;

class TournamentCards extends Widget
{
    protected static string $view = 'filament.widgets.tournament-cards';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 2;

    public function getViewData(): array
    {
        $tournaments = Tournament::whereIn('status', ['registration_open', 'in_progress', 'registration_closed'])
            ->withCount([
                'registrations as registered_count' => fn($q) => $q->whereIn('status', ['registered', 'checked_in']),
                'registrations as checked_in_count' => fn($q) => $q->where('status', 'checked_in'),
            ])
            ->orderBy('start_date', 'asc')
            ->limit(12)
            ->get();

        $cashGames = CashGame::whereIn('status', ['open', 'active', 'running'])
            ->withCount([
                'seats as active_seats_count' => fn($q) => $q->whereIn('status', ['seated', 'playing']),
            ])
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();

        return [
            'tournaments' => $tournaments,
            'cashGames' => $cashGames,
        ];
    }
}

