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
        Schema::create('vehicle_occupations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('vehicle_type_id')->constrained('vehicle_types');
            $table->string('occupation_id');
            $table->boolean('required')->default(true);
            $table->boolean('identical')->default(true);
            $table->boolean('active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_occupations');
    }
};
