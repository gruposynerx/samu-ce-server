<?php

use App\Enums\BPAReportStatusEnum;
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
        Schema::create('bpa_reports', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->jsonb('data')->nullable();
            $table->tinyInteger('status')->default(BPAReportStatusEnum::PENDING);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bpa_reports');
    }
};
