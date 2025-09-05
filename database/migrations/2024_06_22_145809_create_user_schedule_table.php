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
        Schema::create('user_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('users');
            $table->foreignUuid('schema_id')->constrained('user_schedule_schemas');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('occupation_code');
            $table->timestamps();
        });

        Schema::table('user_schedules', function ($table) {
            $table->index('user_id', algorithm: 'hash');
            $table->index('schema_id', algorithm: 'hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_schedules');
    }
};
