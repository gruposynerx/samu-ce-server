<?php

namespace App\Events\RefreshAttendance;

use App\Enums\AttendanceStatusEnum;
use App\Http\Resources\PrimaryAttendanceResource;
use App\Models\PrimaryAttendance;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RefreshPrimaryAttendance implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $results;

    /**
     * Create a new event instance.
     */
    public function __construct(PrimaryAttendance $primaryAttendance, string $action)
    {
        $primaryAttendance = $primaryAttendance->load([
            'attendable.patient',
            'attendable.ticket.requester',
            'attendable.latestUserAttendance' => function ($query) {
                $query->with('user:users.id,users.name')->whereHas('attendance', function ($query) {
                    $query->whereIn('attendance_status_id', AttendanceStatusEnum::ALL_STATUSES_IN_ATTENDANCE);
                });
            },
            'attendable.ticket.city:cities.id,cities.name',
            'attendable.latestMedicalRegulation',
            'attendable.radioOperation.vehicles.vehicleType',
            'attendable.radioOperation.vehicles.base.city',
            'attendable.latestMedicalRegulation.diagnosticHypotheses',
            'attendable.latestMedicalRegulation.createdBy:users.id,users.name',
            'attendable.latestUserAttendance.user',
            'attendable.sceneRecording:scene_recordings.attendance_id,scene_recordings.id,scene_recordings.unit_destination_id,scene_recordings.created_at,scene_recordings.created_by,scene_recordings.priority_type_id',
            'attendable.sceneRecording.latestDestinationUnitHistory:scene_recording_destination_unit_histories.id,scene_recording_destination_unit_histories.unit_destination_id,scene_recording_destination_unit_histories.scene_recording_id',
            'attendable.sceneRecording.latestDestinationUnitHistory.unitDestination:units.id,units.name',
            'attendable.sceneRecording.unitDestination:units.id,units.name',
            'attendable.sceneRecording.createdBy:users.id,users.name',
            'unitDestination:id,name',
            'attendable.sceneRecording.diagnosticHypotheses',
            'attendable.sceneRecording.latestDestinationUnitHistory',
        ]);

        $this->results = [
            'attendable' => new PrimaryAttendanceResource($primaryAttendance),
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
            new PrivateChannel('primary-attendance.refresh.' . auth()->user()->urc_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'RefreshPrimaryAttendance';
    }
}
