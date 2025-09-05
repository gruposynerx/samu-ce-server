<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleConsultationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'vt_type' => $this->vt_type,
            'total_attendances' => $this->total_attendances,
            'primary_attendances' => $this->primary_attendances,
            'secondary_attendances' => $this->secondary_attendances
        ];
    }
}
