<?php

namespace Database\Seeders;

use App\Enums\ConductEnum;
use App\Models\Conduct;
use Illuminate\Database\Seeder;

class ConductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = ConductEnum::cases();

        foreach ($cases as $case) {
            Conduct::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
