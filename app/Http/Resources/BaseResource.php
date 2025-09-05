<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
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
            'unit_type_id' => $this->unit_type_id,
            'city_id' => $this->city_id,
            'urc_id' => $this->urc_id,
            'name' => $this->name,
            'national_health_registration' => $this->national_health_registration,
            'street' => $this->street,
            'house_number' => $this->house_number,
            'zip_code' => $this->zip_code,
            'neighborhood' => $this->neighborhood,
            'complement' => $this->complement,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'telephone' => $this->telephone,
            'company_registration_number' => $this->company_registration_number,
            'company_name' => $this->company_name,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'vehicle_type_id' => $this->vehicle_type_id,
            'regional_group_id' => $this->regional_group_id,
            'city' => new CityResource($this->whenLoaded('city')),
            'unit_type' => $this->whenLoaded('unitType'),
            'urgency_regulation_center' => new UrgencyRegulationCenterResource($this->whenLoaded('urgencyRegulationCenter')),
            'vehicles' => new VehicleCollection($this->whenLoaded('vehicles')),
            'vehicle_type' => $this->whenLoaded('vehicleType'),
            'regional_group' => new RegionalGroupResource($this->whenLoaded('regionalGroup')),
            'schedules_schemas' => $this->whenLoaded('schedulesSchemas'),
        ];
    }
}
