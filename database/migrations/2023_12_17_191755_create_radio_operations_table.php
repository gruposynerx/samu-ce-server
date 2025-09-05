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
        Schema::create('radio_operations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('attendance_id')->constrained('attendances');
            $table->dateTime('vehicle_requested_at')->nullable();
            $table->dateTime('vehicle_dispatched_at')->nullable();
            $table->dateTime('vehicle_released_at')->nullable();

            $table->dateTime('arrived_to_site_at')->nullable();
            $table->dateTime('left_from_site_at')->nullable();

            $table->dateTime('arrived_to_destination_at')->nullable();
            $table->dateTime('release_from_destination_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radio_operations');
    }
};
