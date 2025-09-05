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
        Schema::create('radio_operation_fleets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('radio_operation_id')->constrained('radio_operations');
            $table->foreignUuid('vehicle_id')->constrained('vehicles');
            $table->foreignId('status')->nullable()->constrained('radio_operation_fleet_statuses');
            $table->boolean('finished')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radio_operation_fleets');
    }
};
