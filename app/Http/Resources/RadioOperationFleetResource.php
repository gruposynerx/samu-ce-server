<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RadioOperationFleetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'vehicle_id' => $this->id,
            'occupation_id' => $this->occupation_id,
            'required' => $this->required,
            'role_id' => $this->role_id,
            'vehicle' => new VehicleResource($this->whenLoaded('vehicle')),
            'occupation' => new OccupationResource($this->whenLoaded('occupation')),
        ];
    }
}
