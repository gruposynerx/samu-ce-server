<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IAMConsultationResource extends JsonResource
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
            'ticket_sequence' => $this->ticket_sequence,
            'attendance_patient_sequence' => $this->attendance_patient_sequence,
            'opening_at' => $this->opening_at,
            'patient' => $this->patient,
            'age' => $this->age,
            'gender' => $this->gender,
            'time_unit_id' => $this->time_unit_id,
            'city' => $this->city,
            'cru' => $this->cru,
            'nature' => $this->nature,
            'hd' => $this->hd,
            'regional_group_name' => $this->regional_group_name,
            'base_name' => $this->base_name,
            'vtr' => $this->vtr,
            'recommended' => $this->recommended,
            'applied' => $this->applied,
            'unit_origin_name' => $this->unit_origin_name,
            'unit_destination_name' => $this->unit_destination_name,
        ];
    }
}
