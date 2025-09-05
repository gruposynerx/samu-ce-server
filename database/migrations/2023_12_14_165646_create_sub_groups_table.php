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
        Schema::create('sub_groups', function (Blueprint $table) {
            $table->string('group_code', 2)->comment('CO_GRUPO');
            $table->string('sub_group_code', 2)->comment('CO_SUB_GRUPO');
            $table->string('sub_group_name', 100)->nullable()->comment('NO_SUB_GRUPO');
            $table->smallInteger('active')->nullable()->default(1);
            $table->primary(['group_code', 'sub_group_code'], 'sub_group_pkey');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_groups');
    }
};
