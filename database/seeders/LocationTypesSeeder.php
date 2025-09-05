<?php

namespace Database\Seeders;

use App\Enums\LocationTypeEnum;
use App\Models\LocationType;
use Illuminate\Database\Seeder;

class LocationTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = LocationTypeEnum::cases();

        foreach ($cases as $case) {
            LocationType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
