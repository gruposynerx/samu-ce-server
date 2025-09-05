<?php

namespace Database\Seeders;

use App\Enums\AntecedentTypeEnum;
use App\Models\AntecedentType;
use Illuminate\Database\Seeder;

class AntecedentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = AntecedentTypeEnum::cases();

        foreach ($cases as $case) {
            AntecedentType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
