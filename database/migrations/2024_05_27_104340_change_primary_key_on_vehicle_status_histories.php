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
            $table->dropPrimary('vehicle_status_histories_id_primary');
            $table->dropColumn('id');
        });

        Schema::table('vehicle_status_histories', function (Blueprint $table) {
            $table->id()->first();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_status_histories', function (Blueprint $table) {
            $table->dropPrimary('vehicle_status_histories_pkey');
            $table->dropColumn('id');
        });

        Schema::table('vehicle_status_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
        });
    }
};
