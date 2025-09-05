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
        Schema::table('attendance_observations', function (Blueprint $table) {
            $table->string('creator_occupation_id')->nullable()->after('role_creator_id');
            $table->foreign('creator_occupation_id')->references('code')->on('occupations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_observations', function (Blueprint $table) {
            $table->dropForeign(['creator_occupation_id']);
            $table->dropColumn('creator_occupation_id');
        });
    }
};
