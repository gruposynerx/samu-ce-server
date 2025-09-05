<?php

namespace App\Events\RefreshAttendance;

use App\Http\Resources\SecondaryAttendanceResource;
use App\Models\SecondaryAttendance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RefreshSecondaryAttendance implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $results;

    /**
     * Create a new event instance.
     */
    public function __construct(SecondaryAttendance $secondaryAttendance, string $action)
    {
        $secondaryAttendance = $secondaryAttendance->load([
            'unitOrigin.city:cities.id,cities.name,cities.federal_unit_id',
            'unitOrigin.city.federalUnit:federal_units.id,federal_units.uf',
            'unitDestination.city:cities.id,cities.name,cities.federal_unit_id',
            'unitDestination.city.federalUnit:federal_units.id,federal_units.uf',
            'attendable.radioOperation.vehicles',
            'attendable.ticket.city:cities.id,cities.name',
            'attendable.ticket.createdBy:users.id,users.name',
            'attendable.patient:patients.id,patients.name,patients.gender_code,patients.age,patients.time_unit_id',
            'attendable.ticket.requester:requesters.id,requesters.name,requesters.primary_phone,requesters.secondary_phone,requesters.requester_type_id,requesters.council_number',
            'attendable.sceneRecording:scene_recordings.attendance_id,scene_recordings.id,scene_recordings.unit_destination_id,scene_recordings.diagnostic_hypothesis_id,scene_recordings.created_at,scene_recordings.created_by',
            'attendable.sceneRecording.latestDestinationUnitHistory:scene_recording_destination_unit_histories.id,scene_recording_destination_unit_histories.unit_destination_id,scene_recording_destination_unit_histories.scene_recording_id',
            'attendable.sceneRecording.latestDestinationUnitHistory.unitDestination.city:cities.id,cities.name,cities.federal_unit_id',
            'attendable.sceneRecording.latestDestinationUnitHistory.unitDestination.city.federalUnit:federal_units.id,federal_units.uf',
            'attendable.sceneRecording.unitDestination.city.federalUnit:federal_units.id,federal_units.uf',
            'attendable.sceneRecording.unitDestination.city:cities.id,cities.name,cities.federal_unit_id',
            'attendable.sceneRecording.createdBy:users.id,users.name',
            // 'attendable.latestMedicalRegulation.diagnosticHypotheses',
            // 'attendable.latestMedicalRegulation.createdBy:users.id,users.name',
            // 'attendable.latestUserAttendance.user',
            // 'attendable.medicalRegulations',
            // 'attendable.observations',
            // 'attendable.radioOperation.notes',
        ]);

        $this->results = [
            'attendable' => new SecondaryAttendanceResource($secondaryAttendance),
            'action' => $action,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('secondary-attendance.refresh.' . auth()->user()->urc_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'RefreshSecondaryAttendance';
    }
}
