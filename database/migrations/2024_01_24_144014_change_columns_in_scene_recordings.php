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
        Schema::table('scene_recordings', function (Blueprint $table) {
            $table->unsignedBigInteger('nature_type_id')->nullable()->change();
            $table->unsignedBigInteger('diagnostic_hypothesis_id')->nullable()->change();
            $table->boolean('support_needed')->nullable()->change();
            $table->json('conduct_types')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scene_recordings', function (Blueprint $table) {
            $table->unsignedBigInteger('nature_type_id')->change();
            $table->unsignedBigInteger('diagnostic_hypothesis_id')->change();
            $table->boolean('support_needed')->change();
            $table->json('conduct_types')->change();
        });
    }
};
