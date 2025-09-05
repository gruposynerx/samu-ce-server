<?php

namespace Database\Seeders;

use App\Enums\BaseTypeEnum;
use App\Enums\UnitTypeEnum;
use App\Models\UnitType;
use Illuminate\Database\Seeder;

class UnitTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = UnitTypeEnum::cases();

        foreach ($cases as $case) {
            UnitType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }

        UnitType::firstOrCreate([
            'id' => BaseTypeEnum::MOBILE_PRE_HOSPITAL_EMERGENCY_UNIT->value,
            'name' => BaseTypeEnum::MOBILE_PRE_HOSPITAL_EMERGENCY_UNIT->message(),
        ]);
    }
}
