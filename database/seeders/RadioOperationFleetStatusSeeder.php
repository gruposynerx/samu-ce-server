<?php

namespace Database\Seeders;

use App\Enums\RadioOperationFleetStatusEnum;
use App\Models\RadioOperationFleetStatus;
use Illuminate\Database\Seeder;

class RadioOperationFleetStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = RadioOperationFleetStatusEnum::cases();

        foreach ($statuses as $status) {
            RadioOperationFleetStatus::firstOrCreate([
                'id' => $status->value,
                'name' => $status->message(),
            ]);
        }
    }
}
