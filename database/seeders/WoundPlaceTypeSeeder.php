<?php

namespace Database\Seeders;

use App\Enums\WoundPlaceTypeEnum;
use App\Models\WoundPlaceType;
use Illuminate\Database\Seeder;

class WoundPlaceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = WoundPlaceTypeEnum::cases();

        foreach ($cases as $case) {
            WoundPlaceType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
