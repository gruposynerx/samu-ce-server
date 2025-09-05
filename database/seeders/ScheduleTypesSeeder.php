<?php

namespace Database\Seeders;

use App\Enums\ScheduleTypeEnum;
use App\Models\ScheduleType;
use Illuminate\Database\Seeder;

class ScheduleTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = ScheduleTypeEnum::cases();

        foreach ($cases as $case) {
            ScheduleType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
