<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tournament extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'start_date',
        'end_date',
        'registration_start',
        'registration_end',
        'late_registration_minutes',
        'venue_name',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'tournament_type',
        'game_type',
        'structure',
        'total_tables',
        'seats_per_table',
        'min_players',
        'max_players',
        'buy_in',
        'entry_fee',
        'guaranteed_prize',
        'actual_prize_pool',
        'payout_structure',
        'starting_stack',
        'level_duration',
        'starting_blinds_small',
        'starting_blinds_big',
        'blind_structure',
        'rebuys_allowed',
        'rebuy_levels',
        'rebuy_cost',
        'addon_allowed',
        'addon_cost',
        'addon_chips',
        'bounty_amount',
        'progressive_bounty',
        'waiting_list_enabled',
        'max_waiting_list',
        'allow_early_registration',
        'require_approval',
        'auto_seat_assignment',
        'enable_qr_checkin',
        'status',
        'is_featured',
        'is_published',
        'image_url',
        'banner_url',
        'contact_name',
        'contact_email',
        'contact_phone',
        'rules_url',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'registration_start' => 'datetime',
        'registration_end' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'buy_in' => 'decimal:2',
        'entry_fee' => 'decimal:2',
        'guaranteed_prize' => 'decimal:2',
        'actual_prize_pool' => 'decimal:2',
        'rebuy_cost' => 'decimal:2',
        'addon_cost' => 'decimal:2',
        'bounty_amount' => 'decimal:2',
        'payout_structure' => 'json',
        'blind_structure' => 'json',
        'rebuys_allowed' => 'boolean',
        'addon_allowed' => 'boolean',
        'progressive_bounty' => 'boolean',
        'waiting_list_enabled' => 'boolean',
        'allow_early_registration' => 'boolean',
        'require_approval' => 'boolean',
        'auto_seat_assignment' => 'boolean',
        'enable_qr_checkin' => 'boolean',
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
    ];

    protected $appends = [
        'total_seats',
        'total_buy_in',
        'available_seats',
        'occupied_seats',
        'checked_in_count',
        'waiting_list_count',
        'is_registration_open',
        'registration_status',
        'google_maps_url',
        'days_until_start',
        'image_url_full',
        'banner_url_full',
        'guaranteed_prize_pool',
    ];

    // Relationships
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Alias for backwards compatibility
     */
    public function reservations()
    {
        return $this->registrations();
    }

    // Accessors
    public function getTotalSeatsAttribute()
    {
        return $this->total_tables * $this->seats_per_table;
    }

    public function getTotalBuyInAttribute()
    {
        return $this->buy_in + $this->entry_fee;
    }

    public function getAvailableSeatsAttribute()
    {
        // Use the cached occupied_seats to avoid duplicate query
        $occupied = $this->occupied_seats;
        
        return max(0, $this->getTotalSeatsAttribute() - $occupied);
    }

    public function getOccupiedSeatsAttribute()
    {
        // Use the cached registrations_count if available (from withCount)
        if (isset($this->attributes['registrations_count'])) {
            return $this->attributes['registrations_count'];
        }
        
        return $this->registrations()
            ->whereIn('status', ['registered', 'checked_in'])
            ->count();
    }

    public function getCheckedInCountAttribute()
    {
        // Use the cached checked_in_count if available (from withCount)
        if (isset($this->attributes['checked_in_count'])) {
            return $this->attributes['checked_in_count'];
        }
        
        return $this->registrations()
            ->where('status', 'checked_in')
            ->count();
    }

    public function getWaitingListCountAttribute()
    {
        // Use the cached waiting_list_count if available (from withCount)
        if (isset($this->attributes['waiting_list_count'])) {
            return $this->attributes['waiting_list_count'];
        }
        
        return $this->registrations()
            ->where('status', 'waiting')
            ->count();
    }

    public function getIsRegistrationOpenAttribute()
    {
        if (!$this->is_published || $this->status === 'cancelled') {
            return false;
        }

        $now = now();
        
        // Check registration dates
        if ($this->registration_start && $now->lt($this->registration_start)) {
            return false;
        }
        
        if ($this->registration_end && $now->gt($this->registration_end)) {
            return false;
        }

        return in_array($this->status, ['published', 'registration_open']);
    }

    public function getRegistrationStatusAttribute()
    {
        // If not published or cancelled or draft, registration is closed
        if (!$this->is_published || in_array($this->status, ['cancelled', 'draft', 'completed'])) {
            return 'closed';
        }

        // If explicitly marked as registration closed
        if ($this->status === 'registration_closed') {
            return 'closed';
        }

        $now = now();
        
        // Check if registration hasn't started yet
        if ($this->registration_start && $now->lt($this->registration_start)) {
            return 'closed';
        }
        
        // Check if registration has ended
        if ($this->registration_end && $now->gt($this->registration_end)) {
            return 'closed';
        }

        // Check if tournament is full
        if ($this->available_seats <= 0) {
            return 'full';
        }

        // Check if closing soon (within 24 hours or >70% full)
        $fillPercentage = ($this->occupied_seats / $this->total_seats) * 100;
        $hoursUntilClose = $this->registration_end ? $now->diffInHours($this->registration_end, false) : null;
        
        if ($fillPercentage >= 70 || ($hoursUntilClose !== null && $hoursUntilClose <= 24 && $hoursUntilClose > 0)) {
            return 'closing_soon';
        }

        // Registration is open
        return 'open';
    }

    public function getGoogleMapsUrlAttribute()
    {
        if ($this->latitude && $this->longitude) {
            return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        }
        
        $address = urlencode("{$this->address}, {$this->city}, {$this->country}");
        return "https://www.google.com/maps/search/?api=1&query={$address}";
    }

    public function getDaysUntilStartAttribute()
    {
        return now()->diffInDays($this->start_date, false);
    }

    public function getImageUrlFullAttribute()
    {
        if (!$this->image_url) {
            return null;
        }

        // If already a full URL, return as is
        if (str_starts_with($this->image_url, 'http://') || str_starts_with($this->image_url, 'https://')) {
            return $this->image_url;
        }

        // Add /storage prefix if not present
        $path = $this->image_url;
        if (!str_starts_with($path, '/storage/') && !str_starts_with($path, 'storage/')) {
            $path = '/storage/' . ltrim($path, '/');
        }

        // Return full URL
        return url($path);
    }

    public function getBannerUrlFullAttribute()
    {
        if (!$this->banner_url) {
            return null;
        }

        // If already a full URL, return as is
        if (str_starts_with($this->banner_url, 'http://') || str_starts_with($this->banner_url, 'https://')) {
            return $this->banner_url;
        }

        // Add /storage prefix if not present
        $path = $this->banner_url;
        if (!str_starts_with($path, '/storage/') && !str_starts_with($path, 'storage/')) {
            $path = '/storage/' . ltrim($path, '/');
        }

        // Return full URL
        return url($path);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now())
                    ->orderBy('start_date', 'asc');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['registration_open', 'in_progress']);
    }

    public function scopeRegistrationOpen($query)
    {
        return $query->where('status', 'registration_open')
                    ->where('is_published', true);
    }

    // Methods
    public static function boot()
    {
        parent::boot();

        static::creating(function ($tournament) {
            if (empty($tournament->slug)) {
                $tournament->slug = Str::slug($tournament->name);
            }
        });
    }

    public function canRegister(): bool
    {
        if (!$this->is_registration_open) {
            return false;
        }

        if ($this->available_seats > 0) {
            return true;
        }

        return $this->waiting_list_enabled && 
               (!$this->max_waiting_list || $this->waiting_list_count < $this->max_waiting_list);
    }

    public function updatePrizePool(): void
    {
        $count = $this->registrations()
            ->whereIn('status', ['registered', 'checked_in'])
            ->count();
        
        $this->actual_prize_pool = $count * $this->buy_in;
        $this->save();
    }

    public function getFormattedBuyIn(): string
    {
        return number_format($this->buy_in, 2) . ' + ' . number_format($this->entry_fee, 2);
    }

    public function getFormattedPrize(): string
    {
        $prize = $this->guaranteed_prize ?? $this->actual_prize_pool;
        return number_format($prize, 2);
    }

    public function getGuaranteedPrizePoolAttribute()
    {
        // Return guaranteed prize if set and > 0, otherwise return actual prize pool if > 0
        // If both are null or 0, return 0
        $guaranteed = $this->attributes['guaranteed_prize'] ?? null;
        $actual = $this->attributes['actual_prize_pool'] ?? null;
        
        if ($guaranteed && (float)$guaranteed > 0) {
            return (float)$guaranteed;
        }
        
        if ($actual && (float)$actual > 0) {
            return (float)$actual;
        }
        
        return 0;
    }
}
