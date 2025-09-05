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
        Schema::table('scene_recording_antecedents', function (Blueprint $table) {
            $table->index('scene_recording_id', 'scene_recording_antecedents_scene_recording_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scene_recording_antecedents', function (Blueprint $table) {
            $table->dropIndex('scene_recording_antecedents_scene_recording_id_index');
        });
    }
};
