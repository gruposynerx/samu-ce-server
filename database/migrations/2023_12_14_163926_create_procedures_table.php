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
        Schema::create('procedures', function (Blueprint $table) {
            $table->string('code', 10)->primary()->comment('PROCEDURE_CODE');
            $table->string('code_9', 9)->nullable();
            $table->string('name', 250)->nullable()->comment('PROCEDURE_NAME');
            $table->string('complexity_type', 1)->nullable()->comment('COMPLEIXTY_TYPE');
            $table->string('permitted_gender', 1)->nullable()->comment('PERMITTED_GENDER');
            $table->integer('max_per_patient')->nullable()->comment('MAX_EXECUTION_PER_PATIENT');
            $table->integer('min_age')->nullable()->comment('MINIMUM_AGE');
            $table->integer('max_age')->nullable()->comment('MAXIMUM_AGE');
            $table->char('needs_age', 1)->default('N');
            $table->decimal('unit_value', 10)->nullable()->comment('UNIT_VALUE');
            $table->string('financing_code', 2)->nullable()->comment('PROCEDURES_X_FINANCING_CODE');
            $table->string('rubric', 6)->nullable()->comment('RUBRIC_CODE');
            $table->smallInteger('active')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedures');
    }
};
