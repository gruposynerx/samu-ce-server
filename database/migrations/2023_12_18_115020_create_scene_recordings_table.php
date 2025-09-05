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
        Schema::create('scene_recordings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('urc_id')->constrained('urgency_regulation_centers');
            $table->foreignUuid('attendance_id')->constrained('attendances');
            $table->foreignUuid('created_by')->constrained('users');
            $table->foreignId('nature_type_id')->constrained('nature_types');
            $table->foreignId('diagnostic_hypothesis_id')->constrained('diagnostic_hypotheses');
            $table->text('scene_description')->nullable();
            $table->string('icd_code')->nullable();
            $table->foreignId('victim_type_id')->nullable()->constrained('victim_types');
            $table->foreignId('security_equipment_id')->nullable()->constrained('security_equipment');
            $table->foreignId('bleeding_type_id')->nullable()->constrained('bleeding_types');
            $table->foreignId('sweating_type_id')->nullable()->constrained('sweating_types');
            $table->foreignId('skin_coloration_type_id')->nullable()->constrained('skin_coloration_types');
            $table->foreignId('priority_type_id')->nullable()->constrained('priority_types');
            $table->text('observations')->nullable();
            $table->foreignId('antecedent_type_id')->nullable()->constrained('antecedents_types');
            $table->string('allergy')->nullable();
            $table->boolean('support_needed');
            $table->string('support_needed_description')->nullable();
            $table->json('conduct_types');
            $table->boolean('closed')->nullable();
            $table->foreignId('closing_type_id')->nullable()->constrained('closing_types');
            $table->dateTime('death_at')->nullable();
            $table->string('death_type')->nullable();
            $table->string('death_professional')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scene_recordings');
    }
};
