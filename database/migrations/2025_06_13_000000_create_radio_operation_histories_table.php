<?php

use App\Enums\RadioOperationEventTypeEnum;
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
        Schema::create('radio_operation_histories', function (Blueprint $table) {
            $eventTypes = array_map(fn ($e) => $e->value, RadioOperationEventTypeEnum::cases());

            $table->uuid('id')->primary();
            $table->foreignUuid('radio_operation_id')->constrained('radio_operations')->cascadeOnDelete();
            $table->enum('event_type', $eventTypes);
            $table->dateTime('event_timestamp');
            $table->boolean('sent_by_app')->default(false);
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['radio_operation_id', 'event_type', 'created_at']);
            $table->index(['event_type', 'sent_by_app']);
            $table->index(['radio_operation_id', 'event_timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radio_operation_histories');
    }
};
