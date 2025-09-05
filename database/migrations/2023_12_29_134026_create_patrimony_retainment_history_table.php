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
        Schema::create('patrimony_retainment_histories', function (Blueprint $table) {
            $table->uuid('id');
            $table->foreignUuid('patrimony_id')->constrained('patrimonies');
            $table->string('responsible_professional')->nullable();
            $table->dateTime('retained_at')->nullable();
            $table->foreignUuid('retained_by')->constrained('users');
            $table->dateTime('released_at')->nullable();
            $table->foreignUuid('released_by')->nullable()->constrained('users');
            $table->foreignUuid('attendance_id')->constrained('attendances');
            $table->foreignUuid('radio_operation_id')->constrained('radio_operations');
            $table->foreignUuid('urc_id')->constrained('urgency_regulation_centers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patrimony_retainment_histories');
    }
};
