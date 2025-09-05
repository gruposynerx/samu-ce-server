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
        Schema::create('procedure_descriptions', function (Blueprint $table) {
            $table->string('procedure_code', 10)->nullable()->index('relacionar_procedimentoxdescritivo')->comment('CO_PROCEDIMENTO');
            $table->text('description')->nullable()->comment('DS_PROCEDIMENTO');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedure_descriptions');
    }
};
