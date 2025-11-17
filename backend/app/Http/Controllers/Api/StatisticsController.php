<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\CashGame;
use App\Models\Player;
use App\Models\Registration;
use App\Models\CashGameSeat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class StatisticsController extends Controller
{
    /**
     * Get platform statistics
     */
    public function index()
    {
        // Cache statistics for 5 minutes to reduce database load
        return Cache::remember('platform_statistics', 300, function () {
            // Use database queries instead of loading all records
            $totalTournaments = Tournament::published()->count();

            // Count tournaments with open registration using database queries
            $now = now();
            $openTournaments = Tournament::published()
                ->whereIn('status', ['published', 'registration_open'])
                ->where(function ($query) use ($now) {
                    $query->where('registration_start', '<=', $now)
                          ->orWhereNull('registration_start');
                })
                ->where(function ($query) use ($now) {
                    $query->where('registration_end', '>=', $now)
                          ->orWhereNull('registration_end');
                })
                ->count();

            // Total players registered on the platform
            $totalPlayers = Player::count();

            // Total prize pool from all upcoming/active tournaments
            $totalPrizePool = Tournament::published()
                ->whereIn('status', ['published', 'registration_open', 'in_progress'])
                ->where('start_date', '>=', now()->subDays(1))
                ->sum('guaranteed_prize');

            // Total active registrations
            $activeRegistrations = Registration::whereIn('status', ['registered', 'checked_in', 'waiting'])
                ->count();

            // Cash Game Statistics
            $totalCashGames = CashGame::where('is_published', true)->count();
            
            // Active cash games (status: active, full, or not closed/cancelled/maintenance)
            $activeCashGames = CashGame::where('is_published', true)
                ->whereIn('status', ['active', 'full'])
                ->count();
            
            $totalCashGamePlayers = CashGameSeat::whereIn('status', ['seated', 'playing', 'away'])
                ->count();
            
            // Calculate total pot from all active players' current stacks
            // This is more accurate than using the total_pot field which may not be updated
            $totalCashGamePot = CashGameSeat::whereIn('status', ['seated', 'playing', 'away'])
                ->sum('current_stack');

            return response()->json([
                // Tournament Stats
                'total_tournaments' => $totalTournaments,
                'open_now' => $openTournaments,
                'total_players' => $totalPlayers,
                'active_registrations' => $activeRegistrations,
                'total_prize_pool' => $totalPrizePool,
                
                // Cash Game Stats
                'total_cash_games' => $totalCashGames,
                'active_cash_games' => $activeCashGames,
                'total_cash_game_players' => $totalCashGamePlayers,
                'total_cash_game_pot' => $totalCashGamePot,
                
                // Combined Stats
                'total_events' => $totalTournaments + $totalCashGames,
                'total_active_events' => $openTournaments + $activeCashGames,
            ]);
        });
    }

    /**
     * Get tournament-specific statistics
     */
    public function tournament($id)
    {
        // Eager load all counts to avoid N+1 queries
        $tournament = Tournament::withCount([
            'registrations as registrations_count' => function ($q) {
                $q->whereIn('status', ['registered', 'checked_in']);
            },
            'registrations as checked_in_count' => function ($q) {
                $q->where('status', 'checked_in');
            },
            'registrations as waiting_list_count' => function ($q) {
                $q->where('status', 'waiting');
            }
        ])->findOrFail($id);

        return response()->json([
            'tournament_id' => $tournament->id,
            'tournament_name' => $tournament->name,
            'total_seats' => $tournament->total_seats,
            'available_seats' => $tournament->available_seats,
            'occupied_seats' => $tournament->occupied_seats,
            'checked_in_count' => $tournament->checked_in_count,
            'waiting_list_count' => $tournament->waiting_list_count,
            'registration_status' => $tournament->registration_status,
            'prize_pool' => [
                'guaranteed' => $tournament->guaranteed_prize,
                'actual' => $tournament->actual_prize_pool,
                'total' => max($tournament->guaranteed_prize ?? 0, $tournament->actual_prize_pool ?? 0),
            ],
            'days_until_start' => $tournament->days_until_start,
        ]);
    }
}

