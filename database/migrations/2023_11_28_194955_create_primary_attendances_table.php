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
        Schema::create('primary_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('street', 200)->nullable();
            $table->string('house_number', 20)->nullable();
            $table->string('neighborhood', 100)->nullable();
            $table->string('reference_place', 100)->nullable();
            $table->string('primary_complaint')->nullable();
            $table->text('observations')->nullable();
            $table->foreignId('distance_type_id')->nullable()->constrained('distance_types');
            $table->foreignId('location_type_id')->nullable()->constrained('location_types');
            $table->string('location_type_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('primary_attendances');
    }
};
