<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SceneRecordingResource extends JsonResource
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
            'attendance_id' => $this->attendance_id,
            'scene_description' => $this->scene_description,
            'icd_code' => $this->icd_code,
            'icd_code_label' => $this->icd?->description,
            'victim_type' => $this->victim_type,
            'security_equipment' => $this->security_equipment,
            'bleeding_type_id' => $this->bleeding_type_id,
            'sweating_type_id' => $this->sweating_type_id,
            'skin_coloration_type_id' => $this->skin_coloration_type_id,
            'priority_type_id' => $this->priority_type_id,
            'observations' => $this->observations,
            'antecedent_type_id' => $this->antecedent_type_id,
            'allergy' => $this->allergy,
            'support_needed' => $this->support_needed,
            'support_needed_description' => $this->support_needed_description,
            'is_accident_at_work' => $this->is_accident_at_work,
            'conduct_types' => $this->conduct_types,
            'closed' => $this->closed,
            'closing_type_id' => $this->closing_type_id,
            'closed_justification' => $this->closed_justification,
            'death_at' => $this->death_at,
            'death_type' => $this->death_type,
            'death_professional' => $this->death_professional,
            'death_professional_registration_number' => $this->death_professional_registration_number,
            'unit_destination_id' => $this->unit_destination_id,
            'unit_destination_label' => $this->unitDestination?->name,
            'destination_unit_contact' => $this->destination_unit_contact,
            'vacancy_type_id' => $this->vacancy_type_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'metrics' => $this->whenLoaded('metrics'),
            'wounds' => $this->whenLoaded('wounds'),
            'unit_origin' => new UnitResource($this->whenLoaded('unitOrigin')),
            'procedures' => SceneRecordingProcedureResource::collection($this->whenLoaded('procedures')),
            'medicines' => SceneRecordingMedicineResource::collection($this->whenLoaded('medicines')),
            'conducts' => SceneRecordingConductResource::collection($this->whenLoaded('conducts')),
            'destination_unit_histories' => SceneRecordingDestinationUnitHistoryResource::collection($this->whenLoaded('destinationUnitHistories')),
            'latest_destination_unit_history' => new SceneRecordingDestinationUnitHistoryResource($this->whenLoaded('latestDestinationUnitHistory')),
            'created_by' => new UserResource($this->whenLoaded('createdBy')),
            'antecedents_types' => $this->whenLoaded('antecedentsTypes'),
            'diagnostic_hypotheses' => DiagnosticHypothesisResource::collection($this->whenLoaded('diagnosticHypotheses')),
        ];
    }
}
