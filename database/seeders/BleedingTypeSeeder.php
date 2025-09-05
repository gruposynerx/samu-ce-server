<?php

namespace Database\Seeders;

use App\Enums\BleedingTypeEnum;
use App\Models\BleedingType;
use Illuminate\Database\Seeder;

class BleedingTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = BleedingTypeEnum::cases();

        foreach ($cases as $case) {
            BleedingType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
