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
        Schema::create('power_bi_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('urc_id')->constrained('urgency_regulation_centers');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('power_bi_reports');
    }
};
