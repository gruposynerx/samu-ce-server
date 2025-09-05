<?php

namespace Database\Seeders;

use App\Enums\TimeUnitEnum;
use App\Models\TimeUnit;
use Illuminate\Database\Seeder;

class TimeUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = TimeUnitEnum::cases();

        foreach ($cases as $case) {
            TimeUnit::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
