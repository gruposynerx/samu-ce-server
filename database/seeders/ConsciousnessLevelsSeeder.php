<?php

namespace Database\Seeders;

use App\Enums\ConsciousnessLevelEnum;
use App\Models\ConsciousnessLevel;
use Illuminate\Database\Seeder;

class ConsciousnessLevelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = ConsciousnessLevelEnum::cases();

        foreach ($cases as $case) {
            ConsciousnessLevel::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
