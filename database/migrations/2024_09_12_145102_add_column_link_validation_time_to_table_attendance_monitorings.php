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
        Schema::table('attendance_monitorings', function (Blueprint $table) {
            $table->integer('link_validation_time')->default(24);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_monitorings', function (Blueprint $table) {
            $table->dropColumn('link_validation_time');
        });
    }
};
