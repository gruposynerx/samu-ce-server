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
            $table->uuid('regional_group_id')->nullable()->after('position_jobs_id');
            $table->foreign('regional_group_id')->references('id')->on('regional_groups')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('user_schedules', function (Blueprint $table) {
            $table->dropForeign(['regional_group_id']);
            $table->dropColumn('regional_group_id');
        });
    }
};
