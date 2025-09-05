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
        Schema::table('medical_regulations', function (Blueprint $table) {
            $table->unsignedBigInteger('nature_type_id')->nullable()->change();
            $table->unsignedBigInteger('diagnostic_hypothesis_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_regulations', function (Blueprint $table) {
            $table->unsignedBigInteger('nature_type_id')->nullable(false)->change();
            $table->unsignedBigInteger('diagnostic_hypothesis_id')->nullable(false)->change();
        });
    }
};
