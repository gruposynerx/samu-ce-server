<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyVehicleConfirmationEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $results;

    private string $creatorId;

    public function __construct(string $attendanceId, string $fleetId, string $creatorId, string $number)
    {
        $this->creatorId = $creatorId;
        $this->results = [
            'attendance_id' => $attendanceId,
            'fleet_id' => $fleetId,
            'number' => $number,
            'message' => 'VTR confirmou o atendimento.',
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.notifications.' . $this->creatorId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'NotifyVehicleConfirmation';
    }

    public function broadcastWith(): array
    {
        return $this->results;
    }
}
