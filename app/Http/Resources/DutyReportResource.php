<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DutyReportResource extends JsonResource
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
            'period_type_id' => $this->period_type_id,
            'events' => $this->events,
            'compliments' => $this->compliments,
            'internal_complications' => $this->internal_complications,
            'external_complications' => $this->external_complications,
            'duty_report_type_id' => $this->duty_report_type_id,
            'record_at' => $this->record_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'creator' => $this->whenLoaded('creator'),
            'professionals' => $this->whenLoaded('professionals'),
            'medical_regulators' => $this->whenLoaded('medicalRegulators'),
            'radio_operators' => $this->whenLoaded('radioOperators'),
            'tarms' => $this->whenLoaded('tarms'),
            'urgency_regulation_center' => $this->whenLoaded('urgencyRegulationCenter'),
        ];
    }
}
