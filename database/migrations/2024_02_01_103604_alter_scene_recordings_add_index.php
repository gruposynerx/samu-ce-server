<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('scene_recordings', function ($table) {
            $table->index('attendance_id', algorithm: 'hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scene_recordings', function ($table) {
            $table->dropIndex(['attendance_id']);
        });
    }
};
