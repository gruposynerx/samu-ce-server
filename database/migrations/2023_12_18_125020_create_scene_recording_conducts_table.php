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
        Schema::create('scene_recording_conducts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('scene_recording_id')->constrained('scene_recordings');
            $table->foreignId('conduct_id')->nullable()->constrained('conducts');
            $table->string('conduct_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scene_recording_conducts');
    }
};
