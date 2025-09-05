<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LastOcurrencesPerProfessionalResource extends JsonResource
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
            'attendable_type' => $this->attendable_type,
            'attendance_status_id' => $this->attendance_status_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'last_status_updated_at' => $this->last_status_updated_at,
            'number' => $this->number,
            'patient' => new PatientResource($this->whenLoaded('patient')),
        ];
    }
}
