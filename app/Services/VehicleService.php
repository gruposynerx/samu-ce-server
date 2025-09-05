<?php

namespace App\Services;

use App\Enums\VehicleStatusEnum;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;

class VehicleService
{
    public function check(string $id = null): Collection
    {
        $vehicles = Vehicle::where('base_id', $id)
            ->with(
                'latestVehicleStatusHistory:id,vehicle_status_id,vehicle_id,description',
                'base'
            )
            ->get();

        $vehicles->each(function ($vehicle) {
            if ($vehicle->latestVehicleStatusHistory->vehicle_status_id !== VehicleStatusEnum::COMMITTED->value) {
                $vehicle->update(
                    [
                        'base_id' => null,
                    ]
                );
            }
        });

        return $vehicles;
    }

    public function baseHasVehicles(string $baseId, string $vehicleId): Collection
    {
        $validation = Vehicle::where('base_id', $baseId)
            ->with('base')
            ->whereNot('id', $vehicleId)
            ->get();

        return $validation;
    }
}
