<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleTrackingResource extends JsonResource
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
            'code' => $this->code,
            'license_plate' => $this->license_plate,
            'base_id' => $this->base_id,
            'description' => $this->description,
            'general_availability' => $this->general_availability,
            'tracking_device_imei' => $this->tracking_device_imei,
            'base' => new BaseResource($this->whenLoaded('base')),
            'patrimonies' => PatrimonyResource::collection($this->whenLoaded('patrimonies')),
            'vehicle_status_history' => new VehicleStatusHistoryResource($this->whenLoaded('vehicleStatusHistory')),
            'latest_vehicle_status_history' => new VehicleStatusHistoryResource($this->whenLoaded('latestVehicleStatusHistory')),
            'vehicle_type' => $this->whenLoaded('vehicleType'),
            'distance_km' => $this->distance_km,
            'last_position' => $this->last_position,
            'has_tracking' => $this->has_tracking,
            'has_base' => $this->has_base,
            'vehicle_status_priority' => $this->vehicle_status_priority,
        ];
    }
}