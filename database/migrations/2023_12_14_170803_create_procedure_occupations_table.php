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
        Schema::create('procedure_occupations', function (Blueprint $table) {
            $table->string('procedure_code', 10)->comment('CO_PROCEDIMENTO');
            $table->string('occupation_code', 6)->index()->comment('CO_OCUPACAO');
            $table->primary(['procedure_code', 'occupation_code'], 'procedure_occupations_pkey');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedure_occupations');
    }
};
