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
        Schema::create('radio_operation_fleet_histories', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('radio_operation_id')->constrained('radio_operations');
            $table->json('fleet');
            $table->string('change_reason');
            $table->foreignUuid('previous_fleet_creator')->constrained('users');
            $table->foreignUuid('created_by')->constrained('users');
            $table->dateTime('previous_vehicle_requested_at')->nullable();
            $table->dateTime('previous_vehicle_dispatched_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('radio_operation_fleet_histories');
    }
};
