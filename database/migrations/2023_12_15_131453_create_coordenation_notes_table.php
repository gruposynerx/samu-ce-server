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
        Schema::create('coordination_notes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('note');
            $table->foreignUuid('urc_id')->constrained('urgency_regulation_centers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coordination_notes');
    }
};
