<?php

namespace App\Console\Commands;

use App\Enums\VehicleTypeEnum;
use App\Models\Base;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class FillBaseTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-base-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets base types based on the name of the base';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Base::withoutGlobalScopes()->get()->each(function (Base $base) {
            $type = match (true) {
                Str::contains($base->name, 'USB') => VehicleTypeEnum::BASIC_SUPPORT_UNIT,
                Str::contains($base->name, 'USA') => VehicleTypeEnum::ADVANCED_SUPPORT_UNIT,
                Str::contains($base->name, 'AEROMEDICO') => VehicleTypeEnum::AEROMEDICAL,
                Str::contains($base->name, 'MOTOLANCIA') => VehicleTypeEnum::MOTORCYCLE_AMBULANCE,
                default => null,
            };

            $base->update([
                'vehicle_type_id' => $type,
            ]);
        });
    }
}
