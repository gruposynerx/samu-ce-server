<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function addForeignConstraint(string $table): void
    {
        Schema::table($table, function (Blueprint $table) {
            $table->dropForeign(['attendance_id']);

            $table->foreign('attendance_id')
                ->references('id')->on('attendances')
                ->cascadeOnDelete();
        });
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->addForeignConstraint('scene_recordings');
        $this->addForeignConstraint('radio_operations');
        $this->addForeignConstraint('user_attendances');
        $this->addForeignConstraint('medical_regulations');
        $this->addForeignConstraint('attendance_observations');
        $this->addForeignConstraint('vehicle_status_histories');
        $this->addForeignConstraint('patrimony_retainment_histories');
        $this->addForeignConstraint('attendance_cancellation_records');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
