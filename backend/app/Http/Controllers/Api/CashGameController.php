<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashGame;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CashGameController extends Controller
{
    /**
     * Display a listing of cash games
     */
    public function index(Request $request): JsonResponse
    {
        $query = CashGame::query();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by published
        if ($request->has('published')) {
            $query->where('is_published', $request->boolean('published'));
        } else {
            $query->where('is_published', true);
        }

        // Filter by featured
        if ($request->has('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('venue_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Get cash games with seat counts
        $cashGames = $query->withCount([
            'seats as active_seats_count' => function ($q) {
                $q->whereIn('status', ['seated', 'playing', 'away']);
            },
            'seats as waiting_count' => function ($q) {
                $q->where('status', 'waiting');
            }
        ])
        ->orderBy('is_featured', 'desc')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($cashGame) {
            return [
                'id' => $cashGame->id,
                'name' => $cashGame->name,
                'slug' => $cashGame->slug,
                'description' => $cashGame->description,
                'table_number' => $cashGame->table_number,
                'seats_per_table' => $cashGame->seats_per_table,
                'stakes_display' => $cashGame->stakes_display,
                'small_blind' => $cashGame->small_blind,
                'big_blind' => $cashGame->big_blind,
                'min_buy_in' => $cashGame->min_buy_in,
                'max_buy_in' => $cashGame->max_buy_in,
                'default_buy_in' => $cashGame->default_buy_in,
                'game_type' => $cashGame->game_type,
                'status' => $cashGame->status,
                'is_featured' => $cashGame->is_featured,
                'image_url' => $cashGame->image_url,
                'image_url_full' => $cashGame->image_url_full,
                'venue_name' => $cashGame->venue_name,
                'address' => $cashGame->address,
                'city' => $cashGame->city,
                'country' => $cashGame->country,
                'opens_at' => $cashGame->opens_at?->toIso8601String(),
                'closes_at' => $cashGame->closes_at?->toIso8601String(),
                'enable_waiting_list' => $cashGame->enable_waiting_list,
                'max_waiting_list' => $cashGame->max_waiting_list,
                'active_seats_count' => $cashGame->active_seats_count ?? 0,
                'waiting_count' => $cashGame->waiting_count ?? 0,
                'available_seats' => $cashGame->available_seats,
                'fill_percentage' => $cashGame->fill_percentage,
                'is_open' => $cashGame->is_open,
                'created_at' => $cashGame->created_at->toIso8601String(),
                'updated_at' => $cashGame->updated_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $cashGames,
            'count' => $cashGames->count(),
        ]);
    }

    /**
     * Display featured cash games
     */
    public function featured(): JsonResponse
    {
        $cashGames = Cache::remember('cash_games_featured', 600, function () {
            return CashGame::where('is_featured', true)
                ->where('is_published', true)
                ->whereIn('status', ['active', 'full'])
                ->withCount([
                    'seats as active_seats_count' => function ($q) {
                        $q->whereIn('status', ['seated', 'playing', 'away']);
                    }
                ])
                ->orderBy('created_at', 'desc')
                ->limit(6)
                ->get()
                ->map(function ($cashGame) {
                    return [
                        'id' => $cashGame->id,
                        'name' => $cashGame->name,
                        'slug' => $cashGame->slug,
                        'stakes_display' => $cashGame->stakes_display,
                        'image_url_full' => $cashGame->image_url_full,
                        'status' => $cashGame->status,
                        'available_seats' => $cashGame->available_seats,
                        'fill_percentage' => $cashGame->fill_percentage,
                        'default_buy_in' => $cashGame->default_buy_in,
                        'venue_name' => $cashGame->venue_name,
                        'active_seats_count' => $cashGame->active_seats_count ?? 0,
                        'current_players' => $cashGame->active_seats_count ?? 0,
                        'seats_per_table' => $cashGame->seats_per_table,
                        'min_buy_in' => $cashGame->min_buy_in,
                        'max_buy_in' => $cashGame->max_buy_in,
                        'game_type' => $cashGame->game_type,
                        'enable_waiting_list' => $cashGame->enable_waiting_list,
                        'table_number' => $cashGame->table_number,
                    ];
                });
        });

        return response()->json([
            'success' => true,
            'data' => $cashGames,
        ]);
    }

    /**
     * Display active cash games
     */
    public function active(): JsonResponse
    {
        $cashGames = Cache::remember('cash_games_active', 300, function () {
            return CashGame::where('is_published', true)
                ->whereIn('status', ['active', 'full'])
                ->withCount([
                    'seats as active_seats_count' => function ($q) {
                        $q->whereIn('status', ['seated', 'playing', 'away']);
                    }
                ])
                ->orderBy('is_featured', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(12)
                ->get()
                ->map(function ($cashGame) {
                    return [
                        'id' => $cashGame->id,
                        'name' => $cashGame->name,
                        'slug' => $cashGame->slug,
                        'stakes_display' => $cashGame->stakes_display,
                        'image_url_full' => $cashGame->image_url_full,
                        'status' => $cashGame->status,
                        'available_seats' => $cashGame->available_seats,
                        'fill_percentage' => $cashGame->fill_percentage,
                        'default_buy_in' => $cashGame->default_buy_in,
                        'venue_name' => $cashGame->venue_name,
                        'table_number' => $cashGame->table_number,
                        'active_seats_count' => $cashGame->active_seats_count ?? 0,
                        'current_players' => $cashGame->active_seats_count ?? 0,
                        'seats_per_table' => $cashGame->seats_per_table,
                        'min_buy_in' => $cashGame->min_buy_in,
                        'max_buy_in' => $cashGame->max_buy_in,
                        'game_type' => $cashGame->game_type,
                        'enable_waiting_list' => $cashGame->enable_waiting_list,
                    ];
                });
        });

        return response()->json([
            'success' => true,
            'data' => $cashGames,
        ]);
    }

    /**
     * Display the specified cash game
     */
    public function show($id): JsonResponse
    {
        try {
            $cashGame = CashGame::withCount([
                'seats as active_seats_count' => function ($q) {
                    $q->whereIn('status', ['seated', 'playing', 'away']);
                },
                'seats as waiting_count' => function ($q) {
                    $q->where('status', 'waiting');
                }
            ])->findOrFail($id);

            // Check if user is already seated/waiting (if authenticated)
            $userSeat = null;
            if (auth()->check()) {
                $userSeat = $cashGame->seats()
                    ->where('player_id', auth()->id())
                    ->whereIn('status', ['seated', 'playing', 'away', 'waiting'])
                    ->first();
            }

            $data = [
                'id' => $cashGame->id,
                'name' => $cashGame->name,
                'slug' => $cashGame->slug,
                'description' => $cashGame->description,
                'table_number' => $cashGame->table_number,
                'seats_per_table' => $cashGame->seats_per_table,
                'stakes_display' => $cashGame->stakes_display,
                'small_blind' => $cashGame->small_blind,
                'big_blind' => $cashGame->big_blind,
                'min_buy_in' => $cashGame->min_buy_in,
                'max_buy_in' => $cashGame->max_buy_in,
                'default_buy_in' => $cashGame->default_buy_in,
                'game_type' => $cashGame->game_type,
                'structure' => $cashGame->structure,
                'status' => $cashGame->status,
                'is_featured' => $cashGame->is_featured,
                'image_url' => $cashGame->image_url,
                'image_url_full' => $cashGame->image_url_full,
                'venue_name' => $cashGame->venue_name,
                'address' => $cashGame->address,
                'city' => $cashGame->city,
                'state' => $cashGame->state,
                'country' => $cashGame->country,
                'postal_code' => $cashGame->postal_code,
                'google_maps_url' => $cashGame->google_maps_url,
                'opens_at' => $cashGame->opens_at?->toIso8601String(),
                'closes_at' => $cashGame->closes_at?->toIso8601String(),
                'enable_waiting_list' => $cashGame->enable_waiting_list,
                'max_waiting_list' => $cashGame->max_waiting_list,
                'active_seats_count' => $cashGame->active_seats_count ?? 0,
                'waiting_count' => $cashGame->waiting_count ?? 0,
                'available_seats' => $cashGame->available_seats,
                'fill_percentage' => $cashGame->fill_percentage,
                'is_open' => $cashGame->is_open,
                'user_is_seated' => $userSeat ? 1 : 0,
                'user_seat' => $userSeat ? [
                    'id' => $userSeat->id,
                    'seat_number' => $userSeat->seat_number,
                    'status' => $userSeat->status,
                    'waiting_position' => $userSeat->waiting_position,
                    'current_stack' => $userSeat->current_stack,
                ] : null,
                'created_at' => $cashGame->created_at->toIso8601String(),
                'updated_at' => $cashGame->updated_at->toIso8601String(),
            ];

            return response()->json([
                'success' => true,
                'cash_game' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cash game not found',
            ], 404);
        }
    }

    /**
     * Get cash game table layout.
     */
    public function tables($id): JsonResponse
    {
        $cashGame = CashGame::with(['seats.player:id,first_name,last_name'])->findOrFail($id);

        // Get all active seats
        $activeSeats = $cashGame->seats()
            ->whereIn('status', ['seated', 'playing', 'away', 'sitting_out'])
            ->orderBy('seat_number')
            ->get()
            ->keyBy('seat_number');

        $seats = [];
        for ($seatNum = 1; $seatNum <= $cashGame->seats_per_table; $seatNum++) {
            $seat = $activeSeats->get($seatNum);

            $seats[] = [
                'seat_number' => $seatNum,
                'occupied' => $seat !== null,
                'status' => $seat?->status,
                'player_name' => $seat ? ($seat->player->first_name . ' ' . substr($seat->player->last_name, 0, 1) . '.') : null,
                'current_stack' => $seat?->current_stack ?? 0,
                'buy_in_amount' => $seat?->buy_in_amount ?? 0,
            ];
        }

        $occupiedCount = collect($seats)->where('occupied', true)->count();

        return response()->json([
            'cash_game' => [
                'id' => $cashGame->id,
                'name' => $cashGame->name,
                'table_number' => $cashGame->table_number,
                'seats_per_table' => $cashGame->seats_per_table,
            ],
            'table' => [
                'table_number' => $cashGame->table_number,
                'seats' => $seats,
                'occupied_count' => $occupiedCount,
                'total_seats' => $cashGame->seats_per_table,
            ],
        ]);
    }

    /**
     * Get waiting list for a cash game.
     */
    public function waitingList($id): JsonResponse
    {
        $cashGame = CashGame::findOrFail($id);

        $waitingList = $cashGame->seats()
            ->with('player:id,first_name,last_name,email')
            ->where('status', 'waiting')
            ->orderBy('waiting_position', 'asc')
            ->get()
            ->map(function ($seat) {
                return [
                    'id' => $seat->id,
                    'player_name' => $seat->player ? ($seat->player->first_name . ' ' . $seat->player->last_name) : 'Unknown',
                    'email' => $seat->player?->email,
                    'position' => $seat->waiting_position,
                    'buy_in_amount' => $seat->buy_in_amount,
                    'joined_at' => $seat->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'cash_game_id' => $cashGame->id,
            'cash_game_name' => $cashGame->name,
            'waiting_list' => $waitingList,
            'total_count' => $waitingList->count(),
        ]);
    }
}

