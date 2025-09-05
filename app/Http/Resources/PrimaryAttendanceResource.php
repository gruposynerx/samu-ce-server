<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrimaryAttendanceResource extends JsonResource
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
            'street' => $this->street,
            'house_number' => $this->house_number,
            'neighborhood' => $this->neighborhood,
            'reference_place' => $this->reference_place,
            'primary_complaint' => $this->primary_complaint,
            'observations' => $this->observations,
            'distance_type_id' => $this->distance_type_id,
            'location_type_id' => $this->location_type_id,
            'location_type_description' => $this->locationType?->description,
            'in_central_bed' => $this->in_central_bed,
            'in_central_bed_updated_at' => $this->in_central_bed_updated_at,
            'protocol' => $this->protocol,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'attendance' => new AttendanceResource($this->whenLoaded('attendable')),
            'unit_destination' => new UnitResource($this->whenLoaded('unitDestination')),
        ];
    }
}
