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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('mobile_access')->default(false);
            $table->foreignUuid('last_modified_mobile_access_user_id')->nullable()->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('mobile_access');
            $table->dropForeign('last_modified_mobile_access_user_id');
            $table->dropColumn('last_modified_mobile_access_user_id');
        });
    }
};
