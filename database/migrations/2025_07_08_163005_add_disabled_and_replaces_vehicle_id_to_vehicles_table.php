<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->boolean('disabled')->default(false)->after('tracking_system_id');
            $table->uuid('replaces_vehicle_id')->nullable()->after('disabled');

            $table->foreign('replaces_vehicle_id')->references('id')->on('vehicles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['replaces_vehicle_id']);
            $table->dropColumn(['disabled', 'replaces_vehicle_id']);
        });
    }
};
