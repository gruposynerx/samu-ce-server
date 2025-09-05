<?php

namespace Database\Seeders;

use App\Enums\SatisfactionTimeAmbulanceArriveEnum;
use App\Models\SatisfactionTimeAmbulanceArrive;
use Illuminate\Database\Seeder;

class SatisfactionTimeAmbulanceArriveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = SatisfactionTimeAmbulanceArriveEnum::cases();

        foreach ($cases as $case) {
            SatisfactionTimeAmbulanceArrive::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
