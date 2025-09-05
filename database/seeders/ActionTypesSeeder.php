<?php

namespace Database\Seeders;

use App\Enums\ActionTypeEnum;
use App\Models\ActionType;
use Illuminate\Database\Seeder;

class ActionTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = ActionTypeEnum::cases();

        foreach ($cases as $case) {
            ActionType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
