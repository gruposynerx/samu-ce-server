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
        Schema::create('nature_diagnostic_hypotheses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nature_type_id')->constrained('nature_types')->cascadeOnDelete();
            $table->foreignId('diagnostic_hypothesis_id')->constrained('diagnostic_hypotheses')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nature_diagnostic_hypotheses');
    }
};
