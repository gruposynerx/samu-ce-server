<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedure_services', function (Blueprint $table) {
            $table->string('procedure_code', 10)->comment('CO_PROCEDIMENTO');
            $table->string('service_code', 3)->comment('CO_SERVICO');
            $table->string('classification_code', 3)->comment('CO_CLASSIFICACAO');
            $table->primary(['procedure_code', 'service_code', 'classification_code'], 'procedure_services_pkey');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedure_services');
    }
};
