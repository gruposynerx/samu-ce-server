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
            $table->foreignId('vacancy_type_id')->nullable()->constrained('transfer_reasons');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scene_recordings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vacancy_type_id');
        });
    }
};
