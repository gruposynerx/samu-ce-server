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
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('attendable');
            $table->foreignUuid('ticket_id')->nullable()->constrained('tickets');
            $table->foreignUuid('urc_id')->constrained('urgency_regulation_centers');
            $table->foreignUuid('created_by')->constrained('users');
            $table->foreignUuid('patient_id')->nullable()->constrained('patients');
            $table->string('attendance_sequence_per_ticket')->comment('Número sequencial do atendimento, com base no último atendimento criado para o mesmo ticket do atendimento atual');
            $table->foreignId('attendance_status_id')->constrained('attendance_statuses');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
