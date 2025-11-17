<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('registrations', 'player_id')) {
                $table->uuid('player_id')->nullable()->after('id');
                $table->foreign('player_id')
                    ->references('id')
                    ->on('players')
                    ->onDelete('cascade');
                
                $table->index('player_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            if (Schema::hasColumn('registrations', 'player_id')) {
                $table->dropForeign(['player_id']);
                $table->dropIndex(['player_id']);
                $table->dropColumn('player_id');
            }
        });
    }
};

