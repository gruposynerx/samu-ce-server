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
        Schema::create('attendance_evolutions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('form');
            $table->foreignUuid('attendance_id')->constrained('attendances');
            $table->foreignUuid('created_by')->constrained('users');
            $table->text('evolution');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_evolutions');
    }
};
