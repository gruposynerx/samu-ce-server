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
        Schema::table('primary_attendances', function (Blueprint $table) {
            $table->boolean('in_central_bed')->nullable();
            $table->string('protocol')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('primary_attendances', function (Blueprint $table) {
            $table->dropColumn('in_central_bed');
            $table->dropColumn('protocol');
        });
    }
};
