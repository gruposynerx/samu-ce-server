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
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('requester_id')->nullable()->constrained('requesters');
            $table->foreignUuid('urc_id')->constrained('urgency_regulation_centers');
            $table->foreignUuid('created_by')->constrained('users');
            $table->foreignId('ticket_type_id')->constrained('ticket_types');
            $table->foreignId('city_id')->nullable()->constrained('cities');
            $table->boolean('multiple_victims')->nullable();
            $table->smallInteger('number_of_victims')->nullable();
            $table->integer('ticket_sequence_per_urgency_regulation_center')->comment('Número sequencial do ticket, com base no último ticket criado para a base');
            $table->datetime('opening_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
