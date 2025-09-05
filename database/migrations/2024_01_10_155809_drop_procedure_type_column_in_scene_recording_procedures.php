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
        Schema::table('scene_recording_procedures', function (Blueprint $table) {
            $table->dropColumn('procedure_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scene_recording_procedures', function (Blueprint $table) {
            $table->string('procedure_type')->nullable();
        });
    }
};
