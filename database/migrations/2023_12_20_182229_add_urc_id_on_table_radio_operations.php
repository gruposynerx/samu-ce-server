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
        Schema::table('radio_operations', function (Blueprint $table) {
            $table->foreignUuid('urc_id')->nullable()->constrained('urgency_regulation_centers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('radio_operations', function (Blueprint $table) {
            $table->dropForeign(['urc_id']);
            $table->dropColumn('urc_id');
        });
    }
};
