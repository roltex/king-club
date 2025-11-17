<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashGameSeat extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'cash_game_seats';

    protected $fillable = [
        'cash_game_id',
        'player_id',
        'seat_number',
        'buy_in_amount',
        'current_stack',
        'total_profit_loss',
        'status',
        'waiting_position',
        'sat_down_at',
        'left_at',
        'minutes_played',
        'rebuy_count',
        'total_rebuy_amount',
    ];

    protected $casts = [
        'buy_in_amount' => 'decimal:2',
        'current_stack' => 'decimal:2',
        'total_profit_loss' => 'decimal:2',
        'total_rebuy_amount' => 'decimal:2',
        'sat_down_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    // Relationships
    public function cashGame()
    {
        return $this->belongsTo(CashGame::class);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    // Accessors
    public function getTotalInvestedAttribute()
    {
        return $this->buy_in_amount + $this->total_rebuy_amount;
    }

    public function getNetProfitLossAttribute()
    {
        return $this->current_stack - $this->total_invested;
    }

    // Methods
    public function rebuy($amount): void
    {
        $this->current_stack += $amount;
        $this->total_rebuy_amount += $amount;
        $this->rebuy_count += 1;
        $this->save();
    }

    public function updateStack($newStack): void
    {
        $this->current_stack = $newStack;
        $this->total_profit_loss = $this->current_stack - $this->total_invested;
        $this->save();
    }

    public function sitOut(): void
    {
        $this->status = 'sitting_out';
        $this->save();
    }

    public function sitBack(): void
    {
        if ($this->status === 'sitting_out') {
            $this->status = 'playing';
            $this->save();
        }
    }

    public function leave(): void
    {
        $this->status = 'left';
        $this->left_at = now();
        $this->minutes_played = $this->sat_down_at->diffInMinutes(now());
        $this->total_profit_loss = $this->current_stack - $this->total_invested;
        $this->save();
        
        // Update cash game player count
        $this->cashGame->updatePlayerCount();
    }
}

