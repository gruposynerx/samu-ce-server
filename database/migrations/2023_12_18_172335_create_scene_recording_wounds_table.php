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
        Schema::create('scene_recording_wounds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('scene_recording_id')->constrained('scene_recordings');
            $table->foreignId('wound_type_id')->nullable()->constrained('wound_types');
            $table->foreignId('wound_place_type_id')->nullable()->constrained('wound_place_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scene_recording_wounds');
    }
};
