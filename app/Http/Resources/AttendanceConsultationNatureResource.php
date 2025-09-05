<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceConsultationNatureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'nature' => $this->nature,
            'total_attendances' => $this->total_attendances,
            'primary_attendances' => $this->primary_attendances,
            'secondary_attendances' => $this->secondary_attendances,
            'total_attendances_average' => round($this->total_attendances_average, 2),
            'primary_attendances_average' => round($this->primary_attendances_average, 2),
            'secondary_attendances_average' => round($this->secondary_attendances_average, 2),
        ];
    }
}
