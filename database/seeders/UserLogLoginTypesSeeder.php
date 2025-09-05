<?php

namespace Database\Seeders;

use App\Enums\UserLogLoginTypeEnum;
use App\Models\UserLogLoginType;
use Illuminate\Database\Seeder;

class UserLogLoginTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = UserLogLoginTypeEnum::cases();

        foreach ($cases as $case) {
            UserLogLoginType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
