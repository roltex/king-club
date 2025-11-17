<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_games', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // Table Information
            $table->integer('table_number')->unique();
            $table->integer('seats_per_table')->default(9);
            $table->integer('max_players')->default(9);
            $table->integer('min_players')->default(2);
            
            // Stakes (Cash Game Specific)
            $table->decimal('small_blind', 10, 2);
            $table->decimal('big_blind', 10, 2);
            // stakes_display is computed in model accessor
            
            // Buy-In Limits
            $table->decimal('min_buy_in', 10, 2);
            $table->decimal('max_buy_in', 10, 2);
            $table->decimal('default_buy_in', 10, 2); // Suggested buy-in amount
            
            // Rake Structure
            $table->enum('rake_type', [
                'no_rake',
                'time_charge',
                'percentage',
                'cap',
                'no_flop_no_drop'
            ])->default('percentage');
            $table->decimal('rake_percentage', 5, 2)->nullable(); // e.g., 5.00 for 5%
            $table->decimal('rake_cap', 10, 2)->nullable(); // Maximum rake per hand
            $table->decimal('time_charge_amount', 10, 2)->nullable(); // Per hour charge
            $table->integer('time_charge_interval')->nullable(); // Minutes between charges
            
            // Game Type
            $table->enum('game_type', [
                'texas_holdem',
                'omaha',
                'omaha_hilo',
                'plo',
                'plo5',
                'seven_card_stud',
                'razz',
                'horse',
                'mixed_games',
                'short_deck',
                'chinese_poker'
            ])->default('texas_holdem');
            
            $table->enum('structure', [
                'nlhe',           // No-Limit Hold'em
                'limit',          // Limit
                'pot_limit',      // Pot-Limit
                'mixed'
            ])->default('nlhe');
            
            // Location
            $table->string('venue_name');
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Operating Hours
            $table->time('opens_at')->nullable();
            $table->time('closes_at')->nullable();
            $table->json('operating_days')->nullable(); // ['monday', 'tuesday', ...]
            
            // Settings
            $table->boolean('allow_reservations')->default(true);
            $table->boolean('auto_seat_assignment')->default(true);
            $table->boolean('require_approval')->default(false);
            $table->boolean('enable_waiting_list')->default(true);
            $table->integer('max_waiting_list')->nullable();
            $table->boolean('allow_rebuy')->default(true);
            $table->boolean('allow_side_pots')->default(true);
            $table->boolean('show_hand_history')->default(true);
            
            // Status
            $table->enum('status', [
                'draft',
                'active',
                'full',
                'closed',
                'maintenance',
                'cancelled'
            ])->default('draft');
            
            // Statistics
            $table->integer('current_players')->default(0);
            $table->decimal('total_pot', 12, 2)->default(0);
            $table->decimal('total_rake', 12, 2)->default(0);
            $table->integer('hands_played')->default(0);
            $table->dateTime('last_hand_at')->nullable();
            
            // Featured & Visibility
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->string('image_url')->nullable();
            
            // Contact & Support
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('slug');
            $table->index('table_number');
            $table->index('status');
            $table->index('game_type');
            $table->index(['status', 'is_published']);
            $table->index(['small_blind', 'big_blind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_games');
    }
};

