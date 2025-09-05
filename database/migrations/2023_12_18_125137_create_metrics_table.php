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
        Schema::create('metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('metricable');
            $table->dateTime('start_at')->nullable();
            $table->string('diagnostic_evaluation')->nullable();
            $table->integer('systolic_blood_pressure')->nullable();
            $table->integer('diastolic_blood_pressure')->nullable();
            $table->integer('heart_rate')->nullable();
            $table->integer('respiratory_frequency')->nullable();
            $table->decimal('temperature')->nullable();
            $table->integer('oxygen_saturation')->nullable();
            $table->integer('glasgow_scale')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metrics');
    }
};
