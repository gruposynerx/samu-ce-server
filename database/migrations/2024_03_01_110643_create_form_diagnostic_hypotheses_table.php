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
        Schema::create('form_diagnostic_hypotheses', function (Blueprint $table) {
            $table->id();
            $table->uuidMorphs('form');
            $table->foreignId('nature_type_id')->constrained('nature_types');
            $table->foreignId('diagnostic_hypothesis_id')->constrained('diagnostic_hypotheses');
            $table->foreignUuid('created_by')->constrained('users');
            $table->foreignUuid('attendance_id')->constrained('attendances');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_diagnostic_hypotheses');
    }
};
