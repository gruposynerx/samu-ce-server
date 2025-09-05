<?php

namespace Database\Seeders;

use App\Enums\SatisfactionScaleEnum;
use App\Models\SatisfactionScale;
use Illuminate\Database\Seeder;

class SatisfactionScaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = SatisfactionScaleEnum::cases();

        foreach ($cases as $case) {
            SatisfactionScale::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
