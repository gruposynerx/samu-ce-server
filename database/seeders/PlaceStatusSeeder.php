<?php

namespace Database\Seeders;

use App\Enums\PlaceStatusEnum;
use App\Models\PlaceStatus;
use Illuminate\Database\Seeder;

class PlaceStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = PlaceStatusEnum::cases();

        foreach ($cases as $case) {
            PlaceStatus::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
