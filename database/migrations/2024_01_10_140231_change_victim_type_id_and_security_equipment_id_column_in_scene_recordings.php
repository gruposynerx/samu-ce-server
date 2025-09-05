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
            $table->dropForeign(['victim_type_id']);
            $table->dropForeign(['security_equipment_id']);
            $table->dropColumn('victim_type_id', 'security_equipment_id');

            $table->string('victim_type')->nullable();
            $table->string('security_equipment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
