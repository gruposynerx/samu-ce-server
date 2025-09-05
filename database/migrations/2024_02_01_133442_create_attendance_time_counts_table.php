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
        Schema::create('attendance_time_counts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('attendance_id')->constrained('attendances');
            $table->integer('response_time')->comment('Tempo de resposta ao atendimento, baseado na diferença entre a hora de abertura do chamado e a hora de chegada ao local');
            $table->dateTime('response_time_measured_at')->comment('Tempo em que a resposta foi medida');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_time_counts');
    }
};
