<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\Player;
use App\Models\Registration;
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

            return response()->json([
                'total_tournaments' => $totalTournaments,
                'open_now' => $openTournaments,
                'total_players' => $totalPlayers,
                'active_registrations' => $activeRegistrations,
                'total_prize_pool' => $totalPrizePool,
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

