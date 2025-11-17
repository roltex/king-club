<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // Date & Time
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->dateTime('registration_start')->nullable();
            $table->dateTime('registration_end')->nullable();
            $table->integer('late_registration_minutes')->default(0);
            
            // Location
            $table->string('venue_name');
            $table->text('address');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country');
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Tournament Details
            $table->enum('tournament_type', [
                'freezeout',
                'rebuy',
                'addon',
                'bounty',
                'progressive_bounty',
                'turbo',
                'hyper_turbo',
                'deep_stack',
                'shootout',
                'satellite',
                'freeroll',
                'guaranteed',
                'mystery_bounty'
            ])->default('freezeout');
            
            $table->enum('game_type', [
                'texas_holdem',
                'omaha',
                'omaha_hilo',
                'seven_card_stud',
                'razz',
                'horse',
                'mixed_games',
                'plo',
                'plo5',
                'short_deck'
            ])->default('texas_holdem');
            
            $table->enum('structure', [
                'nlhe',           // No-Limit Hold'em
                'limit',          // Limit
                'pot_limit',      // Pot-Limit
                'mixed'
            ])->default('nlhe');
            
            // Tables & Seats
            $table->integer('total_tables');
            $table->integer('seats_per_table')->default(9);
            $table->integer('total_seats')->storedAs('total_tables * seats_per_table');
            $table->integer('min_players')->nullable();
            $table->integer('max_players')->nullable();
            
            // Buy-In & Prize
            $table->decimal('buy_in', 10, 2);
            $table->decimal('entry_fee', 10, 2)->default(0);
            $table->decimal('total_buy_in', 10, 2)->storedAs('buy_in + entry_fee');
            $table->decimal('guaranteed_prize', 12, 2)->nullable();
            $table->decimal('actual_prize_pool', 12, 2)->default(0);
            $table->text('payout_structure')->nullable(); // JSON
            
            // Blinds & Levels
            $table->integer('starting_stack')->default(10000);
            $table->integer('level_duration')->default(20); // minutes
            $table->integer('starting_blinds_small')->default(25);
            $table->integer('starting_blinds_big')->default(50);
            $table->text('blind_structure')->nullable(); // JSON
            
            // Rebuy/Addon Options
            $table->boolean('rebuys_allowed')->default(false);
            $table->integer('rebuy_levels')->nullable();
            $table->decimal('rebuy_cost', 10, 2)->nullable();
            $table->boolean('addon_allowed')->default(false);
            $table->decimal('addon_cost', 10, 2)->nullable();
            $table->integer('addon_chips')->nullable();
            
            // Bounty (if applicable)
            $table->decimal('bounty_amount', 10, 2)->nullable();
            $table->boolean('progressive_bounty')->default(false);
            
            // Settings
            $table->boolean('waiting_list_enabled')->default(true);
            $table->integer('max_waiting_list')->nullable();
            $table->boolean('allow_early_registration')->default(true);
            $table->boolean('require_approval')->default(false);
            $table->boolean('auto_seat_assignment')->default(true);
            $table->boolean('enable_qr_checkin')->default(true);
            
            // Status
            $table->enum('status', [
                'draft',
                'published',
                'registration_open',
                'registration_closed',
                'in_progress',
                'completed',
                'cancelled'
            ])->default('draft');
            
            // Featured & Visibility
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->string('image_url')->nullable();
            $table->string('banner_url')->nullable();
            
            // Contact & Support
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('rules_url')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('slug');
            $table->index('start_date');
            $table->index('status');
            $table->index('tournament_type');
            $table->index('game_type');
            $table->index(['status', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};

