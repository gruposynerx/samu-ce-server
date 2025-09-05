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
        Schema::create('radio_operation_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('radio_operation_id')->constrained('radio_operations');
            $table->dateTime('datetime');
            $table->string('responsible_professional');
            $table->foreignUuid('patrimony_id')->nullable()->constrained('patrimonies');
            $table->text('observation')->nullable();
            $table->foreignUuid('urc_id')->nullable()->constrained('urgency_regulation_centers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radio_operation_notes');
    }
};
