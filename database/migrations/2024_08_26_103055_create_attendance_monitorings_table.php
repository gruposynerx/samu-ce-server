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
        Schema::create('attendance_monitorings', function (Blueprint $table) {
            $table->uuid('id')->primary()->unique();
            $table->foreignUuid('attendance_id')->constrained('attendances');
            $table->dateTime('attendance_requested_at')->nullable();
            $table->dateTime('vehicle_dispatched_at')->nullable();
            $table->dateTime('in_attendance_at')->nullable();
            $table->dateTime('attendance_completed_at')->nullable();
            $table->integer('link_validation_time')->default(24); // default 24 hours
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_monitorings');
    }
};
