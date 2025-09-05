<?php

namespace Database\Seeders;

use App\Enums\SweatingTypeEnum;
use App\Models\SweatingType;
use Illuminate\Database\Seeder;

class SweatingTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = SweatingTypeEnum::cases();

        foreach ($cases as $case) {
            SweatingType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
