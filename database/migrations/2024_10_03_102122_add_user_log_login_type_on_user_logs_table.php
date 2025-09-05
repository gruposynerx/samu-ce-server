<?php

use App\Enums\UserLogLoginTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Artisan::call('db:seed', ['--class' => 'UserLogLoginTypesSeeder']);
        Schema::table('user_logs', function (Blueprint $table) {
            $table->foreignId('user_log_login_type_id')->default(UserLogLoginTypeEnum::COMPLETED_SUCCESSFULLY)->constrained('user_log_login_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_logs', function (Blueprint $table) {
            $table->dropForeign('user_log_login_type_id');
            $table->dropColumn('user_log_login_type_id');
        });
    }
};
