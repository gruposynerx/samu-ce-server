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
        Schema::create('vehicle_status_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vehicle_id')->constrained('vehicles');
            $table->foreignId('vehicle_status_id')->constrained('vehicle_statuses');
            $table->foreignUuid('user_id')->constrained('users');
            $table->foreignUuid('attendance_id')->nullable()->constrained('attendances');
            $table->string('description', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_status_histories');
    }
};
