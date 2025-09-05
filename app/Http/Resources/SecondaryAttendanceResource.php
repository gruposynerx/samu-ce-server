<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecondaryAttendanceResource extends JsonResource
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
            'observations' => $this->observations,
            'transfer_reason_id' => $this->transfer_reason_id,
            'in_central_bed' => $this->in_central_bed,
            'in_central_bed_updated_at' => $this->in_central_bed_updated_at,
            'protocol' => $this->protocol,
            'diagnostic_hypothesis' => $this->diagnostic_hypothesis,
            'complement_origin' => $this->complement_origin,
            'complement_destination' => $this->complement_destination,
            'requested_resource_id' => $this->requested_resource_id,
            'transfer_observation' => $this->transfer_observation,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'origin_unit_contact' => $this->origin_unit_contact,
            'destination_unit_contact' => $this->destination_unit_contact,
            'unit_origin' => new UnitResource($this->whenLoaded('unitOrigin')),
            'unit_destination' => new UnitResource($this->whenLoaded('unitDestination')),
            'attendance' => new AttendanceResource($this->whenLoaded('attendable')),
        ];
    }
}
