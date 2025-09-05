<?php

namespace Database\Seeders;

use App\Enums\TransferReasonEnum;
use App\Models\TransferReason;
use Illuminate\Database\Seeder;

class TransferReasonsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cases = TransferReasonEnum::cases();

        foreach ($cases as $case) {
            TransferReason::firstOrCreate([
                'id' => $case->value,
                'name' => $case->message(),
            ]);
        }
    }
}
