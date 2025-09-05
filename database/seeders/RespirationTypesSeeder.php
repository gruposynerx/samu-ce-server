<?php

namespace Database\Seeders;

use App\Enums\RespirationTypeEnum;
use App\Models\RespirationType;
use Illuminate\Database\Seeder;

class RespirationTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = RespirationTypeEnum::cases();

        foreach ($cases as $case) {
            RespirationType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
