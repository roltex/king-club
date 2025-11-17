<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'registrations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tournament_id',
        'player_id',
        'first_name',
        'last_name',
        'phone',
        'email',
        'status',
        'table_number',
        'seat_number',
        'waiting_position',
        'qr_code',
        'qr_checksum',
        'checkin_time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'checkin_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the tournament that owns the registration.
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Get the player that owns the registration.
     */
    public function player()
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        // Use player name if available, otherwise use registration name
        if ($this->player) {
            return $this->player->full_name;
        }
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Scope for registered (confirmed) entries
     */
    public function scopeRegistered($query)
    {
        return $query->where('status', 'registered');
    }

    /**
     * Scope for waiting list
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    /**
     * Scope for checked in
     */
    public function scopeCheckedIn($query)
    {
        return $query->where('status', 'checked_in');
    }

    /**
     * Scope for active registrations (registered or checked in)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['registered', 'checked_in']);
    }

    /**
     * Check if registration is checked in
     */
    public function isCheckedIn(): bool
    {
        return $this->status === 'checked_in';
    }

    /**
     * Check if registration is on waiting list
     */
    public function isWaiting(): bool
    {
        return $this->status === 'waiting';
    }

    /**
     * Check if registration is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if registration is confirmed/registered
     */
    public function isRegistered(): bool
    {
        return $this->status === 'registered';
    }

    /**
     * Check if registration is active (registered or checked in)
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['registered', 'checked_in']);
    }
}

