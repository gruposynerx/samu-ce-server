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
        Schema::table('scene_recording_counterreferrals', static function (Blueprint $table) {
        });

        Schema::rename('scene_recording_counterreferrals', 'scene_recording_destination_unit_histories');

        Schema::table('scene_recording_destination_unit_histories', static function (Blueprint $table) {
            $table->unsignedBigInteger('reason_id')->nullable()->change();
            $table->boolean('is_counter_reference')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scene_recording_destination_unit_histories', static function (Blueprint $table) {
            $table->unsignedBigInteger('reason_id')->change();
            $table->dropColumn('is_counter_reference');
        });

        Schema::rename('scene_recording_destination_unit_histories', 'scene_recording_counterreferrals');
    }
};
