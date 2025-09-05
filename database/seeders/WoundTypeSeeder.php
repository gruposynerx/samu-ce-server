<?php

namespace Database\Seeders;

use App\Enums\WoundTypeEnum;
use App\Models\WoundType;
use Illuminate\Database\Seeder;

class WoundTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = WoundTypeEnum::cases();

        foreach ($cases as $case) {
            WoundType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
