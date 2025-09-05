<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MetricResource extends JsonResource
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
            'start_at' => $this->start_at,
            'diagnostic_evaluation' => $this->diagnostic_evaluation,
            'systolic_blood_pressure' => $this->systolic_blood_pressure,
            'diastolic_blood_pressure' => $this->diastolic_blood_pressure,
            'heart_rate' => $this->heart_rate,
            'respiratory_frequency' => $this->respiratory_frequency,
            'temperature' => $this->temperature,
            'oxygen_saturation' => $this->oxygen_saturation,
            'glasgow_scale' => $this->glasgow_scale,
            'created_at' => $this->created_at,
        ];
    }
}
