<?php

namespace Database\Seeders;

use App\Enums\CounterreferralReasonTypeEnum;
use App\Models\CounterreferralReasonType;
use Illuminate\Database\Seeder;

class CounterreferralReasonTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = CounterreferralReasonTypeEnum::cases();

        foreach ($cases as $case) {
            CounterreferralReasonType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
