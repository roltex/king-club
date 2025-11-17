<?php

namespace App\Services;

use App\Models\CashGame;
use App\Models\CashGameSeat;
use Exception;

class CashGameRegistrationService
{
    /**
     * Register/Join a player for a cash game
     *
     * @param array $data Player data (player_id required, buy_in_amount)
     * @param string $cashGameId Cash Game UUID
     * @return CashGameSeat
     * @throws Exception
     */
    public function register(array $data, string $cashGameId): CashGameSeat
    {
        $cashGame = CashGame::findOrFail($cashGameId);

        // Check if cash game is open/active
        if (!in_array($cashGame->status, ['open', 'active', 'running'])) {
            throw new Exception('Cash game is not open for joining');
        }

        // Check if player_id is provided
        if (!isset($data['player_id'])) {
            throw new Exception('Player ID is required. Please login first.');
        }

        // Check for duplicate registration by player_id
        $existing = $cashGame->seats()
            ->where('player_id', $data['player_id'])
            ->whereIn('status', ['seated', 'playing', 'away', 'waiting'])
            ->first();

        if ($existing) {
            throw new Exception('You are already seated or waiting for this cash game');
        }

        // Get buy-in amount
        $buyInAmount = $data['buy_in_amount'] ?? $cashGame->default_buy_in ?? $cashGame->min_buy_in;
        
        // Validate buy-in amount
        if ($buyInAmount < $cashGame->min_buy_in) {
            throw new Exception("Buy-in amount must be at least ₾{$cashGame->min_buy_in}");
        }
        
        if ($buyInAmount > $cashGame->max_buy_in) {
            throw new Exception("Buy-in amount cannot exceed ₾{$cashGame->max_buy_in}");
        }

        // Check available seats
        $occupiedSeats = $cashGame->seats()
            ->whereIn('status', ['seated', 'playing', 'away'])
            ->count();

        if ($occupiedSeats >= $cashGame->seats_per_table) {
            // Cash game full - add to waiting list if enabled
            if ($cashGame->enable_waiting_list) {
                return $this->addToWaitingList($data, $cashGame, $buyInAmount);
            }
            throw new Exception('Cash game is full and waiting list is not enabled');
        }

        // Join player with seat assignment
        if ($cashGame->auto_seat_assignment) {
            return $this->joinWithSeatAssignment($data, $cashGame, $buyInAmount);
        }

        // Manual seat assignment (by admin later) - add to waiting list
        return $this->addToWaitingList($data, $cashGame, $buyInAmount);
    }

    /**
     * Join player with automatic seat assignment
     */
    private function joinWithSeatAssignment(array $data, CashGame $cashGame, float $buyInAmount): CashGameSeat
    {
        // Find first available seat
        // Get all occupied seat numbers (filter out NULL values)
        $occupiedSeats = $cashGame->seats()
            ->whereIn('status', ['seated', 'playing', 'away'])
            ->whereNotNull('seat_number')
            ->pluck('seat_number')
            ->filter() // Remove any NULL values that might slip through
            ->unique()
            ->values()
            ->toArray();

        // Find available seats by comparing with all possible seat numbers
        $allSeats = range(1, $cashGame->seats_per_table);
        $availableSeats = array_diff($allSeats, $occupiedSeats);

        if (empty($availableSeats)) {
            // No seats available, add to waiting list
            return $this->addToWaitingList($data, $cashGame, $buyInAmount);
        }

        // Get the first available seat number
        $seatNumber = reset($availableSeats);

        $seat = CashGameSeat::create([
            'cash_game_id' => $cashGame->id,
            'player_id' => $data['player_id'],
            'seat_number' => $seatNumber,
            'buy_in_amount' => $buyInAmount,
            'current_stack' => $buyInAmount,
            'status' => 'seated',
            'sat_down_at' => now(),
        ]);

        return $seat;
    }

    /**
     * Add player to waiting list
     */
    private function addToWaitingList(array $data, CashGame $cashGame, float $buyInAmount): CashGameSeat
    {
        // Check waiting list capacity
        $waitingCount = $cashGame->seats()
            ->where('status', 'waiting')
            ->count();

        if ($cashGame->max_waiting_list && $waitingCount >= $cashGame->max_waiting_list) {
            throw new Exception('Waiting list is full');
        }

        // Get next waiting position
        $waitingPosition = $waitingCount + 1;

        $seat = CashGameSeat::create([
            'cash_game_id' => $cashGame->id,
            'player_id' => $data['player_id'],
            'seat_number' => null, // No seat assigned yet
            'buy_in_amount' => $buyInAmount,
            'current_stack' => 0, // No stack until seated
            'status' => 'waiting',
            'waiting_position' => $waitingPosition,
            'sat_down_at' => now(),
        ]);

        return $seat;
    }

    /**
     * Move player from waiting list to a seat
     */
    public function moveFromWaitingList(CashGameSeat $seat, int $seatNumber): CashGameSeat
    {
        if ($seat->status !== 'waiting') {
            throw new Exception('Player is not in waiting list');
        }

        $cashGame = $seat->cashGame;

        // Validate seat number
        if ($seatNumber < 1 || $seatNumber > $cashGame->seats_per_table) {
            throw new Exception('Invalid seat number');
        }

        // Check if seat is available
        $existing = $cashGame->seats()
            ->where('seat_number', $seatNumber)
            ->whereIn('status', ['seated', 'playing', 'away'])
            ->first();

        if ($existing) {
            throw new Exception('Seat is already occupied');
        }

        // Move to seat
        $seat->seat_number = $seatNumber;
        $seat->status = 'seated';
        $seat->current_stack = $seat->buy_in_amount;
        $seat->waiting_position = null;
        $seat->save();

        // Reorder waiting list
        $this->reorderWaitingList($cashGame);

        return $seat;
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
     * Leave cash game
     */
    public function leave(CashGameSeat $seat): CashGameSeat
    {
        $seat->status = 'left';
        $seat->left_at = now();
        $seat->minutes_played = $seat->sat_down_at->diffInMinutes(now());
        $seat->total_profit_loss = $seat->current_stack - ($seat->buy_in_amount + $seat->total_rebuy_amount);
        $seat->save();

        return $seat;
    }
}

