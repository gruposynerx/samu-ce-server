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
        Schema::table('user_schedules', function (Blueprint $table) {
            $table->uuid('position_jobs_id')->nullable()->after('shift_id');
            $table->foreign('position_jobs_id')->references('id')->on('position_jobs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_schedules', function (Blueprint $table) {
            $table->dropForeign(['position_jobs_id']);
            $table->dropColumn('position_jobs_id');
        });
    }
};