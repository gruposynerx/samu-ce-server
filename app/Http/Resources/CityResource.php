<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
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
            'federal_unit_id' => $this->federal_unit_id,
            'name' => $this->name,
            'ibge_code' => $this->ibge_code,
            'federal_unit' => new FederalUnitResource($this->whenLoaded('federalUnit')),
            'vehicles' => VehicleResource::collection($this->whenLoaded('vehicles')),
            'telephone_code' => $this->telephone_code,
            'slug' => $this->slug,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
