<?php

namespace Database\Seeders;

use App\Enums\DutyReportTypeEnum;
use App\Models\DutyReportType;
use Illuminate\Database\Seeder;

class DutyReportTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = DutyReportTypeEnum::cases();

        foreach ($cases as $case) {
            DutyReportType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
