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
        Schema::create('bases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuId('urc_id')->constrained('urgency_regulation_centers');
            $table->foreignId('unit_type_id')->constrained('unit_types')->nullable();
            $table->foreignId('city_id')->nullable()->constrained('cities');
            $table->string('name');
            $table->string('national_health_registration', 40);
            $table->string('street', 100)->nullable();
            $table->string('house_number', 20)->nullable();
            $table->string('zip_code')->nullable();
            $table->string('neighborhood', 100)->nullable();
            $table->string('complement')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('company_registration_number', 40)->nullable();
            $table->string('company_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bases');
    }
};
