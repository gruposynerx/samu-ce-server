<?php

namespace Database\Seeders;

use App\Enums\TicketTypeEnum;
use App\Models\TicketType;
use Illuminate\Database\Seeder;

class TicketTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = TicketTypeEnum::cases();

        foreach ($cases as $case) {
            TicketType::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
