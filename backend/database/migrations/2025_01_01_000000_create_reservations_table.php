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
        Schema::create('reservations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->unique();
            $table->string('email')->nullable();
            $table->enum('status', ['reserved', 'waiting', 'checked_in', 'cancelled'])->default('reserved');
            $table->integer('table_number')->nullable();
            $table->integer('seat_number')->nullable();
            $table->integer('waiting_position')->nullable();
            $table->text('qr_code')->nullable();
            $table->string('qr_checksum')->nullable();
            $table->timestamp('checkin_time')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['table_number', 'seat_number']);
            $table->index('status');
            $table->index('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};

