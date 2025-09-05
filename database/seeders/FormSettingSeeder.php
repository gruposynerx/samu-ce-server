<?php

namespace Database\Seeders;

use App\Models\UrgencyRegulationCenter;
use Illuminate\Database\Seeder;

class FormSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $urgencyRegulationCenters = UrgencyRegulationCenter::all();

        $urgencyRegulationCenters->each(function ($urc) {
            $urc->formsSetting()->firstOrCreate([
                'urc_id' => $urc->id,
            ], [
                'enable_late_occurrence' => false,
            ]);
        });
    }
}
