<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatrimonyRetainmentsResource extends JsonResource
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
            'patrimony_id' => $this->patrimony_id,
            'responsible_professional' => $this->responsible_professional,
            'retained_at' => $this->retained_at,
            'retained_by' => $this->retained_by,
            'released_at' => $this->released_at,
            'released_by' => $this->released_by,
            'attendance_id' => $this->attendance_id,
            'radio_operation_id' => $this->radio_operation_id,
            'urc_id' => $this->urc_id,
            'patrimony' => new PatrimonyResource($this->whenLoaded('patrimony')),
            'attendance' => new AttendanceResource($this->whenLoaded('attendance')),
            'retainer' => new UserResource($this->whenLoaded('retainer')),
        ];
    }
}
