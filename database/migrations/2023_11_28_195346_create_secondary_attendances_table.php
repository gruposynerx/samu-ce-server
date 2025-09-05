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
        Schema::create('secondary_attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('observations')->nullable();
            $table->foreignId('transfer_reason_id')->nullable()->constrained('transfer_reasons');
            $table->boolean('in_central_bed')->nullable();
            $table->string('protocol')->nullable();
            $table->text('diagnostic_hypothesis')->nullable();
            $table->foreignUuid('unit_origin_id')->constrained('units');
            $table->foreignUuid('unit_destination_id')->constrained('units');
            $table->string('complement_origin')->nullable();
            $table->string('complement_destination')->nullable();
            $table->foreignId('requested_resource_id')->nullable()->constrained('resources');
            $table->text('transfer_observation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('secondary_attendances');
    }
};
