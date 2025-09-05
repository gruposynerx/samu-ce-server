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
        Schema::create('user_schedule_schemas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users');
            $table->uuidMorphs('schedulable');
            $table->date('valid_from');
            $table->date('valid_through');
            $table->jsonb('days_of_week');
            $table->string('clock_in', 5);
            $table->string('clock_out', 5);
            $table->foreignId('schedule_type_id')->constrained('schedule_types');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_schedule_schemas');
    }
};
