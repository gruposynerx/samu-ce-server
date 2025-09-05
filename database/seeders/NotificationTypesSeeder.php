<?php

namespace Database\Seeders;

use App\Enums\NotificationTypeEnum;
use App\Models\NotificationType;
use Illuminate\Database\Seeder;

class NotificationTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (NotificationTypeEnum::cases() as $type) {
            NotificationType::firstOrCreate([
                'name' => $type->name(),
                'description' => $type->description(),
            ]);
        }
    }
}
