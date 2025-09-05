<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseScheduleSchemaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => "{$this->vehicleType->name} {$this->city->name}",
            'vehicle_type_id' => $this->vehicle_type_id,
            'vehicles' => $this->whenLoaded('vehicles', function () {
                return $this->vehicles->map(function ($vehicle) {
                    return [
                        'id' => $vehicle->id,
                        'code' => $vehicle->code,
                    ];
                });
            }),
            'regional_group' => new RegionalGroupResource($this->whenLoaded('regionalGroup')),
            'scheduled_users' => $this->whenLoaded('scheduledUsers', function () {
                return $this->scheduledUsers->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'phone' => $user->phone,
                        'cbo' => $user->cbo,
                    ];
                });
            }),
        ];
    }
}
