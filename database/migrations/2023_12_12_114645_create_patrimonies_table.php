<?php

use App\Enums\PatrimonyStatusEnum;
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
        Schema::create('patrimonies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('urc_id')->constrained('urgency_regulation_centers');
            $table->foreignUuid('vehicle_id')->nullable()->constrained('vehicles');
            $table->foreignId('patrimony_type_id')->constrained('patrimony_types');
            $table->string('identifier');
            $table->foreignId('patrimony_status_id')->default(PatrimonyStatusEnum::AVAILABLE)->constrained('patrimony_statuses');
            $table->timestamps();

            $table->unique(['identifier', 'patrimony_type_id'], 'patrimonies_identifier_type_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patrimonies');
    }
};
