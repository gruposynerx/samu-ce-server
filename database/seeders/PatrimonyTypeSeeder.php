<?php

namespace Database\Seeders;

use App\Enums\PatrimonyTypeEnum;
use App\Models\PatrimonyType;
use Illuminate\Database\Seeder;

class PatrimonyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = PatrimonyTypeEnum::cases();

        foreach ($cases as $case) {
            PatrimonyType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
