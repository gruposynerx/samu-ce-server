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
        Schema::create('requester_satisfactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('requester_id')->constrained('requesters');
            $table->foreignUuid('attendance_id')->constrained('attendances');
            $table->text('requester_sugestion', 2000)->nullable();
            $table->foreignId('scale_satisfaction_service_offered')->constrained('satisfaction_scales');
            $table->foreignId('scale_attendance_provided_mecs_team')->nullable()->constrained('satisfaction_scales');
            $table->foreignId('scale_telephone_attendance')->nullable()->constrained('satisfaction_scales');
            $table->foreignId('satisfaction_time_ambulance_arrive_id')->nullable()->constrained('satisfaction_time_ambulance_arrive');
            $table->foreignId('satisfaction_time_spent_phone_id')->nullable()->constrained('satisfaction_time_spent_phone');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requester_satisfactions');
    }
};
