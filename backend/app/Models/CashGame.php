<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CashGame extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'table_number',
        'seats_per_table',
        'max_players',
        'min_players',
        'small_blind',
        'big_blind',
        'min_buy_in',
        'max_buy_in',
        'default_buy_in',
        'rake_type',
        'rake_percentage',
        'rake_cap',
        'time_charge_amount',
        'time_charge_interval',
        'game_type',
        'structure',
        'venue_name',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'opens_at',
        'closes_at',
        'operating_days',
        'allow_reservations',
        'auto_seat_assignment',
        'require_approval',
        'enable_waiting_list',
        'max_waiting_list',
        'allow_rebuy',
        'allow_side_pots',
        'show_hand_history',
        'status',
        'current_players',
        'total_pot',
        'total_rake',
        'hands_played',
        'last_hand_at',
        'is_featured',
        'is_published',
        'image_url',
        'contact_name',
        'contact_email',
        'contact_phone',
        'notes',
    ];

    protected $casts = [
        'small_blind' => 'decimal:2',
        'big_blind' => 'decimal:2',
        'min_buy_in' => 'decimal:2',
        'max_buy_in' => 'decimal:2',
        'default_buy_in' => 'decimal:2',
        'rake_percentage' => 'decimal:2',
        'rake_cap' => 'decimal:2',
        'time_charge_amount' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
        'operating_days' => 'array',
        'total_pot' => 'decimal:2',
        'total_rake' => 'decimal:2',
        'last_hand_at' => 'datetime',
        'allow_reservations' => 'boolean',
        'auto_seat_assignment' => 'boolean',
        'require_approval' => 'boolean',
        'enable_waiting_list' => 'boolean',
        'allow_rebuy' => 'boolean',
        'allow_side_pots' => 'boolean',
        'show_hand_history' => 'boolean',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
    ];

    protected $appends = [
        'stakes_display',
        'available_seats',
        'fill_percentage',
        'is_open',
        'google_maps_url',
        'image_url_full',
    ];

    // Relationships
    public function seats()
    {
        return $this->hasMany(CashGameSeat::class);
    }

    public function activeSeats()
    {
        return $this->hasMany(CashGameSeat::class)->whereIn('status', ['seated', 'playing']);
    }

    // Accessors
    public function getStakesDisplayAttribute()
    {
        return "₾{$this->small_blind}/₾{$this->big_blind}";
    }

    public function getAvailableSeatsAttribute()
    {
        $occupied = $this->seats()
            ->whereIn('status', ['seated', 'playing', 'away'])
            ->count();
        
        return max(0, $this->seats_per_table - $occupied);
    }

    public function getFillPercentageAttribute()
    {
        if ($this->seats_per_table == 0) {
            return 0;
        }
        
        $occupied = $this->seats()
            ->whereIn('status', ['seated', 'playing', 'away'])
            ->count();
        
        return round(($occupied / $this->seats_per_table) * 100, 1);
    }

    public function getIsOpenAttribute()
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->opens_at && $this->closes_at) {
            $nowTime = now()->format('H:i:s');
            $opensTime = $this->opens_at->format('H:i:s');
            $closesTime = $this->closes_at->format('H:i:s');
            
            if ($opensTime > $closesTime) {
                // Overnight hours (e.g., 22:00 - 06:00)
                return $nowTime >= $opensTime || $nowTime <= $closesTime;
            } else {
                return $nowTime >= $opensTime && $nowTime <= $closesTime;
            }
        }

        return true;
    }

    public function getGoogleMapsUrlAttribute()
    {
        if ($this->latitude && $this->longitude) {
            return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        }
        
        $address = urlencode("{$this->address}, {$this->city}, {$this->country}");
        return "https://www.google.com/maps/search/?api=1&query={$address}";
    }

    public function getImageUrlFullAttribute()
    {
        if (!$this->image_url) {
            return null;
        }

        if (str_starts_with($this->image_url, 'http://') || str_starts_with($this->image_url, 'https://')) {
            return $this->image_url;
        }

        $path = $this->image_url;
        if (!str_starts_with($path, '/storage/') && !str_starts_with($path, 'storage/')) {
            $path = '/storage/' . ltrim($path, '/');
        }

        return url($path);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'active')
                    ->where('is_published', true);
    }

    // Methods
    public static function boot()
    {
        parent::boot();

        static::creating(function ($cashGame) {
            if (empty($cashGame->slug)) {
                $cashGame->slug = Str::slug($cashGame->name);
            }
        });
    }

    public function canJoin(): bool
    {
        return $this->is_open && 
               $this->available_seats > 0 && 
               $this->status === 'active';
    }

    public function updatePlayerCount(): void
    {
        $this->current_players = $this->seats()
            ->whereIn('status', ['seated', 'playing', 'away'])
            ->count();
        $this->save();
    }
}

