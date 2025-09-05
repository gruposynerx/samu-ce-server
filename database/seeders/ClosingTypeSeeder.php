<?php

namespace Database\Seeders;

use App\Enums\ClosingTypeEnum;
use App\Models\ClosingType;
use Illuminate\Database\Seeder;

class ClosingTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = ClosingTypeEnum::cases();

        foreach ($cases as $case) {
            ClosingType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
