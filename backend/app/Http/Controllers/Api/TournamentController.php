<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TournamentController extends Controller
{
    /**
     * Display a listing of tournaments.
     */
    public function index(Request $request)
    {
        $query = Tournament::query()
            ->published()
            ->withCount(['registrations' => function ($q) {
                $q->whereIn('status', ['registered', 'checked_in']);
            }]);

        // Check if user is authenticated and add their registration status
        $userId = $request->user()?->id;
        if ($userId) {
            $query->withCount(['registrations as user_is_registered' => function ($q) use ($userId) {
                $q->where('player_id', $userId)
                  ->whereIn('status', ['registered', 'waiting', 'checked_in']);
            }]);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by tournament type
        if ($request->has('type')) {
            $query->where('tournament_type', $request->type);
        }

        // Filter by game type
        if ($request->has('game')) {
            $query->where('game_type', $request->game);
        }

        // Search
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('venue_name', 'like', "%{$request->search}%")
                  ->orWhere('city', 'like', "%{$request->search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'start_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginate
        $perPage = $request->get('per_page', 12);
        $tournaments = $query->paginate($perPage);

        return response()->json($tournaments);
    }

    /**
     * Get featured tournaments.
     */
    public function featured()
    {
        // Cache featured tournaments for 10 minutes
        $tournaments = Cache::remember('featured_tournaments', 600, function () {
            return Tournament::query()
                ->published()
                ->featured()
                ->upcoming()
                ->withCount(['registrations' => function ($q) {
                    $q->whereIn('status', ['registered', 'checked_in']);
                }])
                ->limit(6)
                ->get();
        });

        return response()->json($tournaments);
    }

    /**
     * Get upcoming tournaments.
     */
    public function upcoming()
    {
        // Cache upcoming tournaments for 10 minutes
        $tournaments = Cache::remember('upcoming_tournaments', 600, function () {
            return Tournament::query()
                ->published()
                ->upcoming()
                ->withCount(['registrations' => function ($q) {
                    $q->whereIn('status', ['registered', 'checked_in']);
                }])
                ->limit(10)
                ->get();
        });

        return response()->json($tournaments);
    }

    /**
     * Get tournaments open for registration.
     */
    public function open()
    {
        $tournaments = Tournament::query()
            ->registrationOpen()
            ->upcoming()
            ->withCount(['registrations' => function ($q) {
                $q->whereIn('status', ['registered', 'checked_in']);
            }])
            ->get();

        return response()->json($tournaments);
    }

    /**
     * Display the specified tournament.
     */
    public function show(Request $request, $id)
    {
        $query = Tournament::withCount(['registrations' => function ($q) {
                $q->whereIn('status', ['registered', 'checked_in']);
            }]);

        // Check if user is authenticated and add their registration status
        $userId = $request->user()?->id;
        if ($userId) {
            $query->withCount(['registrations as user_is_registered' => function ($q) use ($userId) {
                $q->where('player_id', $userId)
                  ->whereIn('status', ['registered', 'waiting', 'checked_in']);
            }]);
        }

        $tournament = $query->findOrFail($id);

        if (!$tournament->is_published) {
            return response()->json([
                'message' => 'Tournament not found or not published.'
            ], 404);
        }

        return response()->json($tournament);
    }

    /**
     * Get tournament by slug.
     */
    public function showBySlug($slug)
    {
        $tournament = Tournament::where('slug', $slug)
            ->withCount(['registrations' => function ($q) {
                $q->whereIn('status', ['registered', 'checked_in']);
            }])
            ->firstOrFail();

        if (!$tournament->is_published) {
            return response()->json([
                'message' => 'Tournament not found or not published.'
            ], 404);
        }

        return response()->json($tournament);
    }

    /**
     * Get tournament statistics.
     */
    public function statistics($id)
    {
        $tournament = Tournament::findOrFail($id);

        $stats = [
            'tournament_id' => $tournament->id,
            'tournament_name' => $tournament->name,
            'total_seats' => $tournament->total_seats,
            'available_seats' => $tournament->available_seats,
            'occupied_seats' => $tournament->occupied_seats,
            'checked_in_count' => $tournament->checked_in_count,
            'waiting_list_count' => $tournament->waiting_list_count,
            'registration_open' => $tournament->is_registration_open,
            'can_register' => $tournament->canRegister(),
            'prize_pool' => [
                'guaranteed' => $tournament->guaranteed_prize,
                'actual' => $tournament->actual_prize_pool,
                'total' => max($tournament->guaranteed_prize ?? 0, $tournament->actual_prize_pool),
            ],
            'days_until_start' => $tournament->days_until_start,
        ];

        return response()->json($stats);
    }

    /**
     * Get tournament table layout.
     */
    public function tables($id)
    {
        $tournament = Tournament::with(['registrations' => function ($q) {
            $q->whereIn('status', ['registered', 'checked_in'])
              ->orderBy('table_number')
              ->orderBy('seat_number');
        }])->findOrFail($id);

        $tables = [];
        for ($tableNum = 1; $tableNum <= $tournament->total_tables; $tableNum++) {
            $seats = [];
            for ($seatNum = 1; $seatNum <= $tournament->seats_per_table; $seatNum++) {
                $registration = $tournament->registrations->where('table_number', $tableNum)
                                                       ->where('seat_number', $seatNum)
                                                       ->first();

                $seats[] = [
                    'seat_number' => $seatNum,
                    'occupied' => $registration !== null,
                    'status' => $registration?->status,
                    'player_name' => $registration ? $registration->player->first_name . ' ' . substr($registration->player->last_name, 0, 1) . '.' : null,
                ];
            }

            $tables[] = [
                'table_number' => $tableNum,
                'seats' => $seats,
                'occupied_count' => count(array_filter($seats, fn($s) => $s['occupied'])),
            ];
        }

        return response()->json([
            'tournament' => [
                'id' => $tournament->id,
                'name' => $tournament->name,
                'total_tables' => $tournament->total_tables,
                'seats_per_table' => $tournament->seats_per_table,
            ],
            'tables' => $tables,
        ]);
    }

    /**
     * Get tournament types.
     */
    public function types()
    {
        return response()->json([
            'tournament_types' => [
                'freezeout' => 'Freezeout (No Rebuys)',
                'rebuy' => 'Rebuy',
                'addon' => 'Add-on',
                'bounty' => 'Bounty',
                'progressive_bounty' => 'Progressive Bounty',
                'turbo' => 'Turbo',
                'hyper_turbo' => 'Hyper Turbo',
                'deep_stack' => 'Deep Stack',
                'shootout' => 'Shootout',
                'satellite' => 'Satellite',
                'freeroll' => 'Freeroll',
                'guaranteed' => 'Guaranteed',
                'mystery_bounty' => 'Mystery Bounty',
            ],
            'game_types' => [
                'texas_holdem' => "Texas Hold'em",
                'omaha' => 'Omaha',
                'omaha_hilo' => 'Omaha Hi-Lo',
                'seven_card_stud' => '7-Card Stud',
                'razz' => 'Razz',
                'horse' => 'HORSE',
                'mixed_games' => 'Mixed Games',
                'plo' => 'Pot-Limit Omaha',
                'plo5' => '5-Card PLO',
                'short_deck' => 'Short Deck (6+)',
            ],
        ]);
    }

    /**
     * Get registered players for a tournament.
     */
    public function registeredPlayers(string $id)
    {
        $tournament = Tournament::findOrFail($id);

        $registrations = $tournament->registrations()
            ->with('player:id,first_name,last_name,email,city,country')
            ->whereIn('status', ['registered', 'checked_in'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($registration) {
                return [
                    'id' => $registration->id,
                    'player_name' => $registration->full_name,
                    'email' => $registration->email,
                    'city' => $registration->player?->city,
                    'country' => $registration->player?->country,
                    'status' => $registration->status,
                    'table_number' => $registration->table_number,
                    'seat_number' => $registration->seat_number,
                    'registered_at' => $registration->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'tournament_id' => $tournament->id,
            'tournament_name' => $tournament->name,
            'registered_players' => $registrations,
            'total_count' => $registrations->count(),
        ]);
    }

    /**
     * Get waiting list for a tournament.
     */
    public function tournamentWaitingList(string $id)
    {
        $tournament = Tournament::findOrFail($id);

        $waitingList = $tournament->registrations()
            ->with('player:id,first_name,last_name,email,city,country')
            ->where('status', 'waiting')
            ->orderBy('waiting_position', 'asc')
            ->get()
            ->map(function ($registration) {
                return [
                    'id' => $registration->id,
                    'player_name' => $registration->full_name,
                    'email' => $registration->email,
                    'city' => $registration->player?->city,
                    'country' => $registration->player?->country,
                    'position' => $registration->waiting_position,
                    'registered_at' => $registration->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'tournament_id' => $tournament->id,
            'tournament_name' => $tournament->name,
            'waiting_list' => $waitingList,
            'total_count' => $waitingList->count(),
        ]);
    }
}
