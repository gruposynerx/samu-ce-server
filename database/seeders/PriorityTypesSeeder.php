<?php

namespace Database\Seeders;

use App\Enums\PriorityTypeEnum;
use App\Models\PriorityType;
use Illuminate\Database\Seeder;

class PriorityTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = PriorityTypeEnum::cases();

        foreach ($cases as $case) {
            PriorityType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
