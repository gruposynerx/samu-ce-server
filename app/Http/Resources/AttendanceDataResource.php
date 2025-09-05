<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceDataResource extends JsonResource
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
            'number' => $this->number,
            'attendance_sequence_per_ticket' => $this->attendance_sequence_per_ticket,
            'is_late_occurrence' => $this->is_late_occurrence,
            'creator' => new UserResource($this->whenLoaded('createdBy')),
            'patient' => new PatientResource($this->whenLoaded('patient')),
            'ticket' => new TicketResource($this->whenLoaded('ticket')),
            'latest_medical_regulation' => new MedicalRegulationResource($this->whenLoaded('latestMedicalRegulation')),
            'radio_operation' => new RadioOperationResource($this->whenLoaded('radioOperation')),
            'scene_recording' => new SceneRecordingResource($this->whenLoaded('sceneRecording')),
            'scene_recordings' => SceneRecordingResource::collection($this->whenLoaded('sceneRecordings')),
            'observations' => AttendanceObservationResource::collection($this->whenLoaded('observations')),
            'attendable' => $this->whenLoaded('attendable'),
            'cancellation' => $this->whenLoaded('cancellation'),
            'vehicles' => $this->whenLoaded('vehicles'),
            'father_link' => new AttendanceDataResource($this->whenLoaded('fatherLink')),
            'latest_vehicle_status_history' => $this->latestVehicleStatusHistory,
        ];
    }
}
