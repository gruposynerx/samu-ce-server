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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('vehicle_type_id')->constrained('vehicle_types');
            $table->foreignUuid('urc_id')->constrained('urgency_regulation_centers');
            $table->string('code')->unique();
            $table->string('license_plate')->unique();
            $table->foreignUuid('base_id')->nullable()->constrained('bases');
            $table->string('chassis')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
