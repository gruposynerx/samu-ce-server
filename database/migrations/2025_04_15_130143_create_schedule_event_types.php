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
        Schema::create('schedule_event_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        DB::table('schedule_event_types')->insert([
            ['name' => 'Troca de escala', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Convocação', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Falta justificada', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_event_types');
    }
};
