<?php

namespace Database\Seeders;

use App\Enums\PatrimonyStatusEnum;
use App\Models\PatrimonyStatus;
use Illuminate\Database\Seeder;

class PatrimonyStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = PatrimonyStatusEnum::cases();

        foreach ($cases as $case) {
            PatrimonyStatus::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
