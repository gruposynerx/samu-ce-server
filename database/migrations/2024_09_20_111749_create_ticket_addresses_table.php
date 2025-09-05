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
        Schema::create('ticket_geolocations', function (Blueprint $table) {
            $table->id();
            $table->string('place_id')->nullable();
            $table->json('address')->nullable();
            $table->json('location')->nullable();
            $table->json('viewport')->nullable();
            $table->string('formatted_address')->nullable();
            $table->foreignUuid('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_geolocations');
    }
};
