<?php

namespace Database\Seeders;

use App\Enums\PeriodTypeEnum;
use App\Models\PeriodType;
use Illuminate\Database\Seeder;

class PeriodTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = PeriodTypeEnum::cases();

        foreach ($cases as $case) {
            PeriodType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
