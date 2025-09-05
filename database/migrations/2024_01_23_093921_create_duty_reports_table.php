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
        Schema::create('duty_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('urc_id')->constrained('urgency_regulation_centers');
            $table->foreignUuid('created_by')->constrained('users');
            $table->foreignId('period_type_id')->constrained('period_types');
            $table->date('record_date');
            $table->text('record');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('duty_reports');
    }
};
