<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRegulationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->whenNotNull($this->id),
            'attendance_id' => $this->whenNotNull($this->attendance_id),
            'medical_regulation' => $this->whenNotNull($this->medical_regulation),
            'priority_type_id' => $this->whenNotNull($this->priority_type_id),
            'consciousness_level_id' => $this->whenNotNull($this->consciousness_level_id),
            'respiration_type_id' => $this->whenNotNull($this->respiration_type_id),
            'action_type_id' => $this->whenNotNull($this->action_type_id),
            'action_details' => $this->whenNotNull($this->action_details),
            'vehicle_movement_code_id' => $this->whenNotNull($this->vehicle_movement_code_id),
            'supporting_organizations' => $this->whenNotNull($this->supporting_organizations),
            'created_at' => $this->whenNotNull($this->created_at),
            'updated_at' => $this->whenNotNull($this->updated_at),
            'destination_unit_contact' => $this->whenNotNull($this->destination_unit_contact),
            'createdBy' => $this->whenLoaded('createdBy'),
            'diagnosticHypothesis' => $this->whenLoaded('diagnosticHypothesis'),
            'diagnostic_hypotheses' => DiagnosticHypothesisResource::collection($this->whenLoaded('diagnosticHypotheses')),
            'evolutions' => AttendanceEvolutionResource::collection($this->whenLoaded('evolutions')),
        ];
    }
}
