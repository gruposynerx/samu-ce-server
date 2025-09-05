<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\LinkEventScheduleEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        
        Schema::table('user_schedules', function (Blueprint $table) {
            $linksNames = array_map(fn ($e) => $e->value, LinkEventScheduleEnum::cases());

            $table->string('observation')->nullable();
            $table->dateTime('prev_start_date')->nullable();
            $table->dateTime('prev_end_date')->nullable();
            $table->foreignUuid('base_id')->nullable()->constrained('bases');
            $table->foreignUuid('urc_id')->nullable()->constrained('urgency_regulation_centers');
            $table->enum('link', $linksNames)->nullable();
            $table->uuid('schema_id')->nullable()->change();
            $table->dateTime('starts_at')->nullable()->change();
            $table->dateTime('ends_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_schedules', function (Blueprint $table) {
            $table->uuid('schema_id')->nullable(false)->change();
            $table->dateTime('starts_at')->nullable(false)->change();
            $table->dateTime('ends_at')->nullable(false)->change();

            $table->dropColumn('link');
            $table->dropColumn(['observation', 'prev_start_date', 'prev_end_date']);

            $table->dropForeign(['urc_id']);
            $table->dropColumn('urc_id');

            $table->dropForeign(['base_id']);
            $table->dropColumn('base_id');
        });
    }
};

