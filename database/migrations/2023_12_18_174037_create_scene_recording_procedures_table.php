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
        Schema::create('scene_recording_procedures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('scene_recording_id')->constrained('scene_recordings');
            $table->string('procedure_code')->nullable();
            $table->string('procedure_type')->nullable();
            $table->string('observations')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scene_recording_procedures');
    }
};
