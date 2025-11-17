<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Schema::table('registrations', function (Blueprint $table) {
                // Add missing tournament_id index for foreign key lookups
                $table->index('tournament_id', 'registrations_tournament_id_idx');
                
                // Add composite index for common queries
                $table->index(['tournament_id', 'status'], 'registrations_tournament_status_idx');
                
                // Add composite index for player lookups
                $table->index(['player_id', 'status'], 'registrations_player_status_idx');
            });
        } catch (\Exception $e) {
            // Indexes might already exist, which is fine
            // SQLite throws exception if index already exists
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('registrations', function (Blueprint $table) {
                $table->dropIndex('registrations_player_status_idx');
                $table->dropIndex('registrations_tournament_status_idx');
                $table->dropIndex('registrations_tournament_id_idx');
            });
        } catch (\Exception $e) {
            // Ignore errors if indexes don't exist
        }
    }
};

