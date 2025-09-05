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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('identifier', 11)->unique();
            $table->string('national_health_card', 15)->nullable();
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->date('birthdate');
            $table->char('gender_code', 1);
            $table->string('phone', 11);
            $table->string('whatsapp', 11);

            $table->string('neighborhood', 100);
            $table->smallInteger('street_type');
            $table->string('street', 100);
            $table->integer('house_number');
            $table->string('complement', 200)->nullable();

            $table->string('council_number', 50)->nullable();
            $table->string('cbo', 10)->nullable();

            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
