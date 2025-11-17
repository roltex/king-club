<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_game_seats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('cash_game_id');
            $table->uuid('player_id');
            $table->integer('seat_number'); // 1-9
            $table->decimal('buy_in_amount', 10, 2);
            $table->decimal('current_stack', 10, 2)->default(0);
            $table->decimal('total_profit_loss', 10, 2)->default(0);
            
            // Status
            $table->enum('status', [
                'seated',
                'playing',
                'away',
                'sitting_out',
                'left',
                'removed'
            ])->default('seated');
            
            // Timestamps
            $table->dateTime('sat_down_at');
            $table->dateTime('left_at')->nullable();
            $table->integer('minutes_played')->default(0);
            
            // Rebuy tracking
            $table->integer('rebuy_count')->default(0);
            $table->decimal('total_rebuy_amount', 10, 2)->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('cash_game_id')->references('id')->on('cash_games')->onDelete('cascade');
            $table->foreign('player_id')->references('id')->on('players')->onDelete('cascade');
            
            // Indexes
            $table->unique(['cash_game_id', 'seat_number']);
            $table->unique(['cash_game_id', 'player_id', 'status']);
            $table->index(['cash_game_id', 'status']);
            $table->index('player_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_game_seats');
    }
};

