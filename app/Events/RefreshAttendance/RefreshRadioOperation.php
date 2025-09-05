<?php

namespace App\Events\RefreshAttendance;

use App\Http\Resources\RadioOperationResource;
use App\Models\RadioOperation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RefreshRadioOperation implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $results;

    /**
     * Create a new event instance.
     */
    public function __construct(?RadioOperation $radioOperation, string $action)
    {
        if ($radioOperation) {
            $radioOperation = $radioOperation->load([
                'vehicles',
                'fleets.users',
                'attendance.patient',
                'attendance.ticket.requester',
                'attendance.ticket.city:cities.id,cities.name',
                'attendance.latestMedicalRegulation',
                'attendance.latestMedicalRegulation.diagnosticHypotheses',
                'attendance.latestMedicalRegulation.createdBy:users.id,users.name',
                'attendance.sceneRecording:scene_recordings.attendance_id,scene_recordings.id',
                'attendance.sceneRecording.latestDestinationUnitHistory',
                'attendance.latestUserAttendance.user',
            ]);
        }

        $this->results = [
            'radio_operation' => $radioOperation ? new RadioOperationResource($radioOperation) : null,
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
            new PrivateChannel('radio-operation.refresh.' . auth()->user()->urc_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'RefreshRadioOperationAttendance';
    }
}
