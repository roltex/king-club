<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashGameSeat;
use App\Models\CashGame;
use App\Services\CashGameRegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CashGameRegistrationController extends Controller
{
    public function __construct(
        private CashGameRegistrationService $registrationService
    ) {}

    /**
     * Register/Join a player for a cash game (requires authentication)
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cash_game_id' => 'required|uuid|exists:cash_games,id',
            'buy_in_amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Get authenticated player
        $player = $request->user();
        
        $cashGameId = $request->cash_game_id;

        // Build data from authenticated player
        $data = [
            'player_id' => $player->id,
            'buy_in_amount' => $request->buy_in_amount,
        ];

        try {
            $seat = $this->registrationService->register($data, $cashGameId);
            $seat->load('cashGame', 'player');

            $response = [
                'success' => true,
                'status' => $seat->status,
                'seat_id' => $seat->id,
                'cash_game' => [
                    'id' => $seat->cashGame->id,
                    'name' => $seat->cashGame->name,
                    'stakes_display' => $seat->cashGame->stakes_display,
                    'table_number' => $seat->cashGame->table_number,
                ],
            ];

            if ($seat->status === 'waiting') {
                $response['waiting_position'] = $seat->waiting_position;
                $response['message'] = 'Cash game is full. You have been added to the waiting list at position ' . $seat->waiting_position;
            } else {
                $response['seat_number'] = $seat->seat_number;
                $response['buy_in_amount'] = $seat->buy_in_amount;
                $response['current_stack'] = $seat->current_stack;
                $response['message'] = 'Successfully joined ' . $seat->cashGame->name;
            }

            return response()->json($response, 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Leave cash game
     */
    public function leave(Request $request, $id): JsonResponse
    {
        try {
            $seat = CashGameSeat::findOrFail($id);
            
            // Verify ownership
            if ($seat->player_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $seat = $this->registrationService->leave($seat);

            return response()->json([
                'success' => true,
                'message' => 'You have left the cash game',
                'seat' => [
                    'id' => $seat->id,
                    'status' => $seat->status,
                    'total_profit_loss' => $seat->total_profit_loss,
                    'minutes_played' => $seat->minutes_played,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get player's cash game seats
     */
    public function mySeats(Request $request): JsonResponse
    {
        $player = $request->user();

        $seats = CashGameSeat::where('player_id', $player->id)
            ->with('cashGame:id,name,stakes_display,table_number,status')
            ->whereIn('status', ['seated', 'playing', 'away', 'waiting'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($seat) {
                return [
                    'id' => $seat->id,
                    'cash_game' => [
                        'id' => $seat->cashGame->id,
                        'name' => $seat->cashGame->name,
                        'stakes_display' => $seat->cashGame->stakes_display,
                        'table_number' => $seat->cashGame->table_number,
                    ],
                    'seat_number' => $seat->seat_number,
                    'status' => $seat->status,
                    'waiting_position' => $seat->waiting_position,
                    'buy_in_amount' => $seat->buy_in_amount,
                    'current_stack' => $seat->current_stack,
                    'total_profit_loss' => $seat->total_profit_loss,
                    'joined_at' => $seat->sat_down_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $seats,
        ]);
    }

    /**
     * Get cash game registrations/seats
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'cash_game_id' => 'nullable|uuid|exists:cash_games,id',
            'status' => 'nullable|in:seated,playing,away,sitting_out,waiting,left,removed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $query = CashGameSeat::with(['cashGame:id,name', 'player:id,first_name,last_name,email']);

        if ($request->has('cash_game_id')) {
            $query->where('cash_game_id', $request->cash_game_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $seats = $query->orderBy('seat_number')
            ->orderBy('waiting_position')
            ->get()
            ->map(function ($seat) {
                return [
                    'id' => $seat->id,
                    'cash_game_id' => $seat->cash_game_id,
                    'player' => [
                        'id' => $seat->player->id,
                        'name' => $seat->player->full_name,
                        'email' => $seat->player->email,
                    ],
                    'seat_number' => $seat->seat_number,
                    'status' => $seat->status,
                    'waiting_position' => $seat->waiting_position,
                    'buy_in_amount' => $seat->buy_in_amount,
                    'current_stack' => $seat->current_stack,
                    'total_profit_loss' => $seat->total_profit_loss,
                    'joined_at' => $seat->sat_down_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $seats,
        ]);
    }
}

