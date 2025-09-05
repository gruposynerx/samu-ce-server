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
        Schema::create('schedule_events', function (Blueprint $table) {
            $table->id();
            $table->string('justification')->nullable();
            $table->string('attachment')->nullable();
        
            $table->foreignId('user_schedule_id')
                ->constrained('user_schedules')
                ->cascadeOnDelete();
        
            $table->foreignId('reverse_user_schedule_id')
                ->nullable();
            $table->foreign('reverse_user_schedule_id')
                ->references('id')->on('user_schedules')
                ->onDelete('cascade');
        
            $table->foreignId('schedule_event_type_id')
                ->constrained('schedule_event_types')
                ->cascadeOnDelete();
        
            $table->uuid('reverse_professional_id')->nullable();
            $table->foreign('reverse_professional_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        
            $table->uuid('professional_id')
                ->constrained('users')
                ->cascadeOnDelete();
        
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_events');
    }
};
