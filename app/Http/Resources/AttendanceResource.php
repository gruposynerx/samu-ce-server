<?php

namespace App\Http\Resources;

use App\Models\PrimaryAttendance;
use App\Models\SecondaryAttendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
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
            'created_by' => $this->created_by,
            'attendance_sequence_per_ticket' => $this->attendance_sequence_per_ticket,
            'attendance_status_id' => $this->attendance_status_id,
            'is_late_occurrence' => $this->is_late_occurrence,
            'number' => $this->number,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'log' => $this->whenLoaded('latestLog'),
            'attendable' => $this->whenLoaded('attendable'),
            'ticket' => new TicketResource($this->whenLoaded('ticket')),
            'patient' => new PatientResource($this->whenLoaded('patient')),
            'scene_recording' => $this->whenLoaded('sceneRecording'),
            'scene_recordings' => $this->whenLoaded('sceneRecordings'),
            'user_attendances' => new UserAttendanceResource($this->whenLoaded('userAttendances')),
            'latest_user_attendance' => new UserAttendanceResource($this->whenLoaded('latestUserAttendance')),
            'medical_regulations' => MedicalRegulationResource::collection($this->whenLoaded('medicalRegulations')),
            'latest_medical_regulation' => new MedicalRegulationResource($this->whenLoaded('latestMedicalRegulation')),
            'first_medical_regulation' => new MedicalRegulationResource($this->whenLoaded('firstMedicalRegulation')),
            'radio_operation' => $this->whenLoaded('radioOperation'),
            'observations' => $this->whenLoaded('observations'),
            'father_link' => new AttendanceResource($this->whenLoaded('fatherLink')),
            'children_links' => new AttendanceResource($this->whenLoaded('childrenLinks')),
            'requester_satisfaction' => new RequesterSatisfactionResource($this->whenLoaded('requesterSatisfaction')),
            'monitoring' => new AttendanceMonitoringResource($this->whenLoaded('monitoring')),
            'attendable_type' => $this->attendable_type,
            'attendable' => $this->when($this->attendable, function () {
                if ($this->attendable instanceof PrimaryAttendance) {
                    return $this->attendable;
                } elseif ($this->attendable instanceof SecondaryAttendance) {
                    return [
                        'id' => $this->attendable->id,
                        'unit_origin' => $this->attendable->unitOrigin,
                        'unit_destination' => $this->attendable->unitDestination,
                    ];
                }

                return $this->attendable;
            }),
        ];
    }
}
