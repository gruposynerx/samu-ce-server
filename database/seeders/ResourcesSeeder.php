<?php

namespace Database\Seeders;

use App\Enums\ResourceEnum;
use App\Models\Resource;
use Illuminate\Database\Seeder;

class ResourcesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = ResourceEnum::cases();

        foreach ($cases as $case) {
            Resource::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
