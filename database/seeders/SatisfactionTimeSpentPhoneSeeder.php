<?php

namespace Database\Seeders;

use App\Enums\SatisfactionTimeSpentPhoneEnum;
use App\Models\SatisfactionTimeSpentPhone;
use Illuminate\Database\Seeder;

class SatisfactionTimeSpentPhoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = SatisfactionTimeSpentPhoneEnum::cases();

        foreach ($cases as $case) {
            SatisfactionTimeSpentPhone::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
