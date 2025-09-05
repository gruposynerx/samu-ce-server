<?php

namespace Database\Seeders;

use App\Enums\RequesterTypeEnum;
use App\Models\RequesterType;
use Illuminate\Database\Seeder;

class RequesterTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = RequesterTypeEnum::cases();

        foreach ($cases as $case) {
            RequesterType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
