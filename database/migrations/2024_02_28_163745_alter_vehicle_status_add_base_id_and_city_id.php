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
            $table->foreignUuid('base_id')->nullable()->constrained('bases');
            $table->foreignId('city_id')->nullable()->constrained('cities');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_status_histories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('base_id');
            $table->dropConstrainedForeignId('city_id');
        });
    }
};
