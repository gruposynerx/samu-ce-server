<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function addForeignConstraint(string $table): void
    {
        Schema::table($table, function (Blueprint $table) {
            $table->dropForeign(['scene_recording_id']);

            $table->foreign('scene_recording_id')
                ->references('id')->on('scene_recordings')
                ->cascadeOnDelete();
        });
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->addForeignConstraint('scene_recording_conducts');
        $this->addForeignConstraint('scene_recording_counterreferrals');
        $this->addForeignConstraint('scene_recording_medicines');
        $this->addForeignConstraint('scene_recording_procedures');
        $this->addForeignConstraint('scene_recording_wounds');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
