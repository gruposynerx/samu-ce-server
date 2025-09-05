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
        Schema::create('place_management_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('place_id')->constrained('place_management');
            $table->foreignUuid('user_id')->nullable()->constrained('users');
            $table->foreignUuid('urc_id')->constrained('urgency_regulation_centers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('place_management_histories');
    }
};
