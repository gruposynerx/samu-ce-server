<?php

namespace App\Http\Resources;

use App\Enums\VehicleTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceConsultationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $vehicleTypeName = isset($this->vehicle_type_id) ? VehicleTypeEnum::tryFrom($this->vehicle_type_id)?->message() : null;

        return [
            'id' => $this->attendance_id,
            'attendance_status_id' => $this->attendance_status_id,
            'attendance_number' => $this->ticket_sequence_per_urgency_regulation_center . '/' . $this->attendance_sequence_per_ticket,
            'opening_at' => $this->opening_at,
            'primary_complaint' => $this->primary_complaint,
            'ticket_type_id' => $this->ticket_type_id,
            'precursor_name' => $this->precursor_name,
            'secondary_attendance_destination_name' => $this->secondary_attendance_destination_name,
            'vehicle' => $this->when(isset($vehicleTypeName), [
                'description' => "$vehicleTypeName $this->code - $this->license_plate",
            ]),
            'requesting' => [
                'name' => $this->requesting_name,
            ],
            'patient' => [
                'name' => $this->patient_name,
                'age' => $this->patient_age,
                'time_unit_id' => $this->patient_time_unit_id,
            ],
            'origin' => [
                'city' => $this->tickets_city_name,
                'neighborhood' => $this->neighborhood,
            ],
            'scene_recording' => $this->when($this->scene_recording_id, [
                'latest_destination_unit_name' => $this->scene_recording_destination_unit_histories_destination_name,
                'priority_type_id' => $this->scene_recording_priority_type_id,
                'creator' => $this->scene_recording_creator_name,
                'created_at' => $this->scene_recording_created_at,
            ]),
            'latest_medical_regulation' => $this->when($this->medical_regulator_name, [
                'priority_type_id' => $this->priority_type_id,
                'creator' => $this->medical_regulator_name,
                'created_at' => $this->medical_regulation_created_at,
            ]),
            'diagnostic_hypotheses' => $this->diagnostic_hypotheses ?? [],
            'father_link' => [
                'id' => $this->father_link_id,
                'number' => $this->father_link_ticket_sequence_per_urgency_regulation_center . '/' . $this->father_link_attendance_sequence_per_ticket,
            ],
        ];
    }
}
