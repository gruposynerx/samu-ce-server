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
        Schema::table('vehicle_status_histories', function (Blueprint $table) {
            $table->index(['attendance_id', 'created_at'], 'vehicle_status_histories_attendance_id_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_status_histories', function (Blueprint $table) {
            $table->dropIndex('vehicle_status_histories_attendance_id_created_at_index');
        });
    }
};
