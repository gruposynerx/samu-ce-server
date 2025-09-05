<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotifyNoVehicleResponseEvent implements ShouldBroadcastNow
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
            'message' => 'Sem resposta da VTR, por favor revise o atendimento.',
        ];
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("user.notifications.{$this->creatorId}");
    }

    public function broadcastAs(): string
    {
        return 'NotifyNoVehicleResponse';
    }

    public function broadcastWith(): array
    {
        return $this->results;
    }
}
