<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceMonitoringResource extends JsonResource
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
            'attendance_id' => $this->attendance_id,
            'attendance_requested_at' => $this->attendance_requested_at,
            'vehicle_dispatched_at' => $this->vehicle_dispatched_at,
            'in_attendance_at' => $this->in_attendance_at,
            'attendance_completed_at' => $this->attendance_completed_at,
            'canceled' => $this->canceled,
            'attendance' => new AttendanceResource($this->whenLoaded('attendance')),
        ];
    }
}
