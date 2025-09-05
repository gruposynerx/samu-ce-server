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
        Schema::table('user_schedule_clocks', function (Blueprint $table) {
             $table->dropForeign(['user_schedule_id']);

             $table->foreignId('user_schedule_id')
                ->nullable()
                ->change();

            $table->foreign('user_schedule_id')
                ->references('id')
                ->on('user_schedules')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_schedule_clocks', function (Blueprint $table) {
            $table->dropForeign(['user_schedule_id']);
            $table->foreignId('user_schedule_id')
                ->nullable(false)
                ->change();
            $table->foreign('user_schedule_id')
                ->references('id')
                ->on('user_schedules')
                ->onDelete('cascade');
        });
    }
};
