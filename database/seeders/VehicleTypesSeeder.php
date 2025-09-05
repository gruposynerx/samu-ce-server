<?php

namespace Database\Seeders;

use App\Enums\VehicleTypeEnum;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class VehicleTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = VehicleTypeEnum::cases();

        foreach ($cases as $case) {
            VehicleType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
