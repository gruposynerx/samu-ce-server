<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
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
            'urc_id' => $this->urc_id,
            'code' => $this->code,
            'license_plate' => $this->license_plate,
            'base_id' => $this->base_id,
            'chassis' => $this->chassis,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'general_availability' => $this->general_availability,
            'tracking_device_imei' => $this->tracking_device_imei,
            'tracking_system_id' => $this->tracking_system_id,
            'base' => new BaseResource($this->whenLoaded('base')),
            'patrimonies' => PatrimonyResource::collection($this->whenLoaded('patrimonies')),
            'vehicle_status_history' => new VehicleStatusHistoryResource($this->whenLoaded('vehicleStatusHistory')),
            'latest_vehicle_status_history' => new VehicleStatusHistoryResource($this->whenLoaded('latestVehicleStatusHistory')),
            'vehicle_type' => $this->whenLoaded('vehicleType'),
        ];
    }
}
