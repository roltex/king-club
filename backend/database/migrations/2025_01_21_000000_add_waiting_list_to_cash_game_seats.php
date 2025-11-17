<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add waiting_position column
        Schema::table('cash_game_seats', function (Blueprint $table) {
            $table->integer('waiting_position')->nullable()->after('status');
        });

        // Update the status enum to include 'waiting'
        // SQLite doesn't support ALTER TYPE, so we need to recreate the table
        if (DB::getDriverName() === 'sqlite') {
            // For SQLite, we'll use a different approach
            // We'll allow NULL seat_number for waiting list entries
            Schema::table('cash_game_seats', function (Blueprint $table) {
                $table->integer('seat_number')->nullable()->change();
            });
            
            // Drop the unique constraint on (cash_game_id, seat_number) to allow NULL seat numbers
            // SQLite doesn't support dropping unique constraints directly, so we'll need to recreate the table
            // But for now, we'll just note that NULL values are allowed
        } else {
            // For other databases, we can alter the enum
            DB::statement("ALTER TABLE cash_game_seats MODIFY COLUMN status ENUM('seated', 'playing', 'away', 'sitting_out', 'left', 'removed', 'waiting') DEFAULT 'seated'");
            Schema::table('cash_game_seats', function (Blueprint $table) {
                $table->integer('seat_number')->nullable()->change();
            });
            
            // Drop unique constraint on (cash_game_id, seat_number) to allow NULL
            DB::statement("ALTER TABLE cash_game_seats DROP INDEX cash_game_seats_cash_game_id_seat_number_unique");
        }
    }

    public function down(): void
    {
        Schema::table('cash_game_seats', function (Blueprint $table) {
            $table->dropColumn('waiting_position');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE cash_game_seats MODIFY COLUMN status ENUM('seated', 'playing', 'away', 'sitting_out', 'left', 'removed') DEFAULT 'seated'");
        }
        
        Schema::table('cash_game_seats', function (Blueprint $table) {
            $table->integer('seat_number')->nullable(false)->change();
        });
    }
};

