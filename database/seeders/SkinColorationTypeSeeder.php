<?php

namespace Database\Seeders;

use App\Enums\SkinColorationTypeEnum;
use App\Models\SkinColorationType;
use Illuminate\Database\Seeder;

class SkinColorationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = SkinColorationTypeEnum::cases();

        foreach ($cases as $case) {
            SkinColorationType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
