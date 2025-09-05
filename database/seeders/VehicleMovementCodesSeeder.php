<?php

namespace Database\Seeders;

use App\Enums\VehicleMovementCodeEnum;
use App\Models\VehicleMovementCode;
use Illuminate\Database\Seeder;

class VehicleMovementCodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = VehicleMovementCodeEnum::cases();

        foreach ($cases as $case) {
            VehicleMovementCode::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
