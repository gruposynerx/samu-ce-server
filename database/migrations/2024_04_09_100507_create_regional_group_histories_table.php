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
        Schema::create('regional_group_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('regional_group_id')->constrained('regional_groups');
            $table->string('previous_regional_group_name');
            $table->string('current_regional_group_name');
            $table->boolean('current_status');
            $table->json('previous_linked_bases');
            $table->json('current_linked_bases');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regional_group_histories');
    }
};
