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
        Schema::create('icds', function (Blueprint $table) {
            $table->string('code', 4)->primary()->comment('CID_CODE');
            $table->string('description', 100)->nullable()->comment('CID_DESCRIPTION');
            $table->string('permitted_gender', 1)->nullable()->comment('GENDER');
            $table->boolean('active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('icds');
    }
};
