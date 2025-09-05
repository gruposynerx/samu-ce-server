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
        Schema::table('duty_reports', static function (Blueprint $table) {
            $table->text('record')->nullable()->change();
            $table->date('record_date')->nullable()->change();
            $table->text('internal_complications')->nullable();
            $table->text('external_complications')->nullable();
            $table->text('compliments')->nullable();
            $table->text('events')->nullable();
            $table->dateTime('record_at')->nullable();
            $table->foreignId('duty_report_type_id')->nullable()->constrained('duty_report_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('duty_reports', static function (Blueprint $table) {
            $table->text('record')->change();
            $table->date('record_date')->change();
            $table->dropColumn('internal_complications');
            $table->dropColumn('external_complications');
            $table->dropColumn('compliments');
            $table->dropColumn('events');
            $table->dropColumn('record_at');
            $table->dropConstrainedForeignId('duty_report_type_id');
        });
    }
};
