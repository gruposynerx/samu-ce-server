<?php

namespace Database\Seeders;

use App\Models\UrgencyRegulationCenter;
use Illuminate\Database\Seeder;

class MonitoringSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $urgencyRegulationCenters = UrgencyRegulationCenter::all();

        $urgencyRegulationCenters->each(function ($urc) {
            $urc->monitoringSetting()->firstOrCreate([
                'urc_id' => $urc->id,
            ], [
                'link_validation_time' => 24,
                'enable_attendance_monitoring' => false,
            ]);
        });
    }
}
