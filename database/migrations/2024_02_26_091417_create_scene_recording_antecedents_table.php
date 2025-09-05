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
        Schema::create('scene_recording_antecedents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('scene_recording_id')->constrained('scene_recordings');
            $table->foreignId('antecedent_type_id')->constrained('antecedents_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scene_recording_antecedents');
    }
};
