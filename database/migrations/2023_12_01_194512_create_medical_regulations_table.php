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
        Schema::create('medical_regulations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('created_by')->constrained('users');
            $table->foreignUuid('attendance_id')->constrained('attendances');
            $table->foreignUuid('urc_id')->constrained('urgency_regulation_centers');
            $table->text('medical_regulation');
            $table->foreignId('nature_type_id')->constrained('nature_types');
            $table->foreignId('diagnostic_hypothesis_id')->constrained('diagnostic_hypotheses');
            $table->foreignId('priority_type_id')->constrained('priority_types');
            $table->foreignId('consciousness_level_id')->nullable()->constrained('consciousness_levels');
            $table->foreignId('respiration_type_id')->nullable()->constrained('respiration_types');
            $table->foreignId('action_type_id')->constrained('action_types');
            $table->json('action_details')->nullable();
            $table->foreignId('vehicle_movement_code_id')->constrained('vehicle_movement_codes');
            $table->json('supporting_organizations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_regulations');
    }
};
