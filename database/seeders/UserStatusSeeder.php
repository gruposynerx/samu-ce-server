<?php

namespace Database\Seeders;

use App\Enums\UserStatusEnum;
use App\Models\UserStatus;
use Illuminate\Database\Seeder;

class UserStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = UserStatusEnum::cases();

        foreach ($cases as $case) {
            UserStatus::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
