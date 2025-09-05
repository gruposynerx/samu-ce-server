<?php

namespace Database\Seeders;

use App\Enums\DistanceTypeEnum;
use App\Models\DistanceType;
use Illuminate\Database\Seeder;

class DistanceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = DistanceTypeEnum::cases();

        foreach ($cases as $case) {
            DistanceType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
