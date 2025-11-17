<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Player extends Authenticatable
{
    use HasFactory, HasUuids, SoftDeletes, HasApiTokens, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'password',
        'date_of_birth',
        'city',
        'country',
        'profile_image',
        'is_active',
        'email_verified',
        'email_verified_at',
        'notes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
        'email_verified' => 'boolean',
        'email_verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the registrations for this player
     */
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get active registrations
     */
    public function activeRegistrations()
    {
        return $this->registrations()
            ->whereIn('status', ['registered', 'checked_in'])
            ->with('tournament');
    }

    /**
     * Get waiting list registrations
     */
    public function waitingRegistrations()
    {
        return $this->registrations()
            ->where('status', 'waiting')
            ->with('tournament');
    }

    /**
     * Check if player is registered for a specific tournament
     */
    public function isRegisteredForTournament(string $tournamentId): bool
    {
        return $this->registrations()
            ->where('tournament_id', $tournamentId)
            ->whereIn('status', ['registered', 'checked_in', 'waiting'])
            ->exists();
    }

    /**
     * Get player's tournament history
     */
    public function tournamentHistory()
    {
        return $this->registrations()
            ->with('tournament')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get statistics for this player
     */
    public function getStatistics(): array
    {
        $totalRegistrations = $this->registrations()->count();
        $checkedIn = $this->registrations()->where('status', 'checked_in')->count();
        $cancelled = $this->registrations()->where('status', 'cancelled')->count();
        $waiting = $this->registrations()->where('status', 'waiting')->count();

        return [
            'total_registrations' => $totalRegistrations,
            'tournaments_played' => $checkedIn,
            'cancelled' => $cancelled,
            'waiting_list' => $waiting,
        ];
    }

    /**
     * Scope to get only active players
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get verified players
     */
    public function scopeVerified($query)
    {
        return $query->where('email_verified', true);
    }
}

