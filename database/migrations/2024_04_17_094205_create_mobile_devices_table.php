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
        Schema::create('mobile_device_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('mobile_device_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('vehicle_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('base_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('edited_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->string('device_mac_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_device_histories');
    }
};
