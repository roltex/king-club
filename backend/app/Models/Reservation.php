<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tournament_id',
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
     * Get the tournament that owns the reservation.
     */
    public function tournament()
    {
        return $this->belongsTo(Tournament::class);
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Scope for reserved seats
     */
    public function scopeReserved($query)
    {
        return $query->where('status', 'reserved');
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
     * Check if reservation is checked in
     */
    public function isCheckedIn(): bool
    {
        return $this->status === 'checked_in';
    }

    /**
     * Check if reservation is on waiting list
     */
    public function isWaiting(): bool
    {
        return $this->status === 'waiting';
    }

    /**
     * Check if reservation is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}

