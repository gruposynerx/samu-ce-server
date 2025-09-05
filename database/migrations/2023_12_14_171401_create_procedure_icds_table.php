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
        Schema::create('procedure_icds', function (Blueprint $table) {
            $table->string('procedure_code', 10)->comment('CO_PROCEDIMENTO');
            $table->string('icd_code', 4)->index()->comment('CO_CID');
            $table->primary(['procedure_code', 'icd_code'], 'procedure_icds_pkey');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedure_icds');
    }
};
