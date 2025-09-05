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
        Schema::create('urgency_regulation_centers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('city_id')->constrained('cities');
            $table->string('name');
            $table->string('street', 200)->nullable();
            $table->string('house_number', 20)->nullable();
            $table->string('neighborhood', 100)->nullable();
            $table->string('reference_place', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('urgency_regulation_centers');
    }
};
