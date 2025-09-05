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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('tracking_device_imei')->nullable();
            $table->index('tracking_device_imei', 'vehicles_tracking_device_imei_index', 'hash');
            $table->integer('tracking_system_id')->nullable();
            $table->index('tracking_system_id', 'vehicles_tracking_system_id_index', 'hash');
            $table->unique(['tracking_device_imei', 'tracking_system_id'], 'vehicles_tracking_device_imei_tracking_system_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropUnique('vehicles_tracking_device_imei_tracking_system_id_unique');
            $table->dropIndex('vehicles_tracking_device_imei_index');
            $table->dropIndex('vehicles_tracking_system_id_index');
            $table->dropColumn('tracking_device_imei');
            $table->dropColumn('tracking_system_id');
        });
    }
};
