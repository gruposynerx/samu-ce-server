<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent WITH SCHEMA pg_catalog;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
