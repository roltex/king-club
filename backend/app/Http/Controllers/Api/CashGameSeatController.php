<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashGameSeat;
use App\Models\CashGame;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CashGameSeatController extends Controller
{
    /**
     * Update seat assignment for a cash game seat
     */
    public function updateSeat(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'seat_number' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $seat = CashGameSeat::findOrFail($id);
            $cashGame = $seat->cashGame;

            // Validate seat number
            if ($request->seat_number > $cashGame->seats_per_table) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid seat number',
                ], 400);
            }

            // If moving to the same seat number, no need to update
            if ($seat->seat_number == $request->seat_number) {
                return response()->json([
                    'success' => true,
                    'message' => 'Seat already assigned',
                    'seat' => [
                        'id' => $seat->id,
                        'seat_number' => $seat->seat_number,
                    ],
                ]);
            }

            // Check if the target seat is already occupied by an active player
            $existingSeat = CashGameSeat::where('cash_game_id', $cashGame->id)
                ->where('seat_number', $request->seat_number)
                ->whereIn('status', ['seated', 'playing', 'away'])
                ->where('id', '!=', $id)
                ->first();

            if ($existingSeat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seat is already occupied',
                ], 400);
            }

            // Use a transaction to handle the unique constraint
            // We need to temporarily set the current seat's seat_number to NULL
            // before assigning the new one to avoid unique constraint violations
            DB::transaction(function () use ($seat, $request, $cashGame) {
                // Store the old seat number
                $oldSeatNumber = $seat->seat_number;
                
                // Temporarily set current seat to NULL to free up the constraint
                $seat->seat_number = null;
                $seat->save();
                
                // Clear any inactive seat that might be using this seat_number
                // (This handles seats with status 'left', 'removed', 'sitting_out', etc.)
                $inactiveSeat = CashGameSeat::where('cash_game_id', $cashGame->id)
                    ->where('seat_number', $request->seat_number)
                    ->whereNotIn('status', ['seated', 'playing', 'away'])
                    ->first();
                
                if ($inactiveSeat) {
                    $inactiveSeat->seat_number = null;
                    $inactiveSeat->save();
                }
                
                // Now assign the new seat number
                $seat->seat_number = $request->seat_number;
                $seat->save();
            });

            return response()->json([
                'success' => true,
                'message' => 'Seat updated successfully',
                'seat' => [
                    'id' => $seat->id,
                    'seat_number' => $seat->seat_number,
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
     * Update status of a cash game seat
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:seated,playing,away,sitting_out,left,removed,waiting',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $seat = CashGameSeat::findOrFail($id);
            $cashGame = $seat->cashGame;
            $oldStatus = $seat->status;
            
            // If moving to 'waiting', clear seat number and set waiting position
            if ($request->status === 'waiting') {
                $waitingCount = $cashGame->seats()
                    ->where('status', 'waiting')
                    ->count();
                
                $seat->status = 'waiting';
                $seat->seat_number = null;
                $seat->waiting_position = $waitingCount + 1;
            } 
            // If moving from 'waiting' to another status, clear waiting position
            elseif ($oldStatus === 'waiting') {
                $seat->status = $request->status;
                $seat->waiting_position = null;
                // Reorder remaining waiting list
                $this->reorderWaitingList($cashGame);
            }
            // If moving to 'left' or 'removed', update timestamps
            elseif (in_array($request->status, ['left', 'removed'])) {
                $seat->status = $request->status;
                $seat->left_at = now();
            } else {
                $seat->status = $request->status;
            }
            
            $seat->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'seat' => [
                    'id' => $seat->id,
                    'status' => $seat->status,
                    'old_status' => $oldStatus,
                    'waiting_position' => $seat->waiting_position,
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
     * Move player from waiting list to a seat
     */
    public function moveFromWaitingList(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'seat_number' => 'required|integer|min:1',
            'buy_in_amount' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $seat = CashGameSeat::findOrFail($id);
            $cashGame = $seat->cashGame;

            // Validate that the seat is in waiting status
            if ($seat->status !== 'waiting') {
                return response()->json([
                    'success' => false,
                    'message' => 'Player is not in waiting list',
                ], 400);
            }

            // Validate seat number
            if ($request->seat_number > $cashGame->seats_per_table) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid seat number',
                ], 400);
            }

            // Check if the target seat is already occupied
            $existingSeat = CashGameSeat::where('cash_game_id', $cashGame->id)
                ->where('seat_number', $request->seat_number)
                ->whereIn('status', ['seated', 'playing', 'away'])
                ->where('id', '!=', $id)
                ->first();

            if ($existingSeat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seat is already occupied',
                ], 400);
            }

            // Move player to seat
            $seat->seat_number = $request->seat_number;
            $seat->status = 'seated';
            $seat->waiting_position = null;
            
            // Set buy-in amount if provided
            if ($request->has('buy_in_amount') && $request->buy_in_amount > 0) {
                $seat->buy_in_amount = $request->buy_in_amount;
                $seat->current_stack = $request->buy_in_amount;
            } elseif (!$seat->buy_in_amount || $seat->buy_in_amount == 0) {
                // Use default buy-in if not set
                $seat->buy_in_amount = $cashGame->default_buy_in ?? $cashGame->min_buy_in;
                $seat->current_stack = $seat->buy_in_amount;
            }
            
            $seat->sat_down_at = now();
            $seat->save();

            // Reorder waiting list
            $this->reorderWaitingList($cashGame);

            return response()->json([
                'success' => true,
                'message' => 'Player moved from waiting list to seat successfully',
                'seat' => [
                    'id' => $seat->id,
                    'seat_number' => $seat->seat_number,
                    'status' => $seat->status,
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
     * Reorder waiting list positions
     */
    private function reorderWaitingList(CashGame $cashGame): void
    {
        $waitingList = $cashGame->seats()
            ->where('status', 'waiting')
            ->orderBy('waiting_position')
            ->orderBy('created_at')
            ->get();

        $position = 1;
        foreach ($waitingList as $waiting) {
            $waiting->update(['waiting_position' => $position++]);
        }
    }

    /**
     * Get all seats for a cash game
     */
    public function index(Request $request): JsonResponse
    {
        $cashGameId = $request->query('cash_game_id');
        $status = $request->query('status');

        $query = CashGameSeat::with(['cashGame', 'player']);

        if ($cashGameId) {
            $query->where('cash_game_id', $cashGameId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $seats = $query->orderBy('seat_number')
            ->get()
            ->map(function ($seat) {
                return [
                    'id' => $seat->id,
                    'seat_number' => $seat->seat_number,
                    'player' => $seat->player ? [
                        'id' => $seat->player->id,
                        'name' => $seat->player->first_name . ' ' . $seat->player->last_name,
                        'email' => $seat->player->email,
                    ] : null,
                    'status' => $seat->status,
                    'current_stack' => $seat->current_stack,
                    'buy_in_amount' => $seat->buy_in_amount,
                    'sat_down_at' => $seat->sat_down_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $seats,
            'count' => $seats->count(),
        ]);
    }
}

