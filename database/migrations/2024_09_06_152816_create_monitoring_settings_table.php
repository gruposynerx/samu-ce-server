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
        Schema::create('monitoring_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('urc_id')->constrained('urgency_regulation_centers');
            $table->integer('link_validation_time')->default(24); // default 24 hours
            $table->boolean('enable_attendance_monitoring', false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_settings');
    }
};
