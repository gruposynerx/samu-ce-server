<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleAverageResponseTimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'vehicle' => $this->vehicle,
            'total_attendances' => $this->total_attendances,
            'primary_attendances' => $this->primary_attendances,
            'secondary_attendances' => $this->secondary_attendances,
            'primary_avg_hours' => $this->primary_avg_hours,
            'secondary_avg_hours' => $this->secondary_avg_hours,
            'total_avg_hours' => $this->total_avg_hours,
        ];
    }
}
