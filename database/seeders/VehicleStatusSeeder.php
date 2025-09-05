<?php

namespace Database\Seeders;

use App\Enums\VehicleStatusEnum;
use App\Models\VehicleStatus;
use Illuminate\Database\Seeder;

class VehicleStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = VehicleStatusEnum::cases();

        foreach ($cases as $case) {
            VehicleStatus::updateOrCreate([
                'id' => $case->value,
            ], ['name' => $case->message()]);
        }
    }
}
