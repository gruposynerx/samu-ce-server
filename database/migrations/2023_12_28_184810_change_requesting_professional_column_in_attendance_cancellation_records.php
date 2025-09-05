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
        Schema::table('attendance_cancellation_records', function (Blueprint $table) {
            $table->string('requesting_professional', 100)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_cancellation_records', function (Blueprint $table) {
            $table->string('requesting_professional', 30)->change();
        });
    }
};
