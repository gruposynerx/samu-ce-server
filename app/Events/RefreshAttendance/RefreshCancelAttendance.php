<?php

namespace App\Events\RefreshAttendance;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RefreshCancelAttendance implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $attendanceId;

    /**
     * Create a new event instance.
     */
    public function __construct($attendanceId)
    {
        $this->attendanceId = $attendanceId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('cancel-attendance.refresh.' . $this->attendanceId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'RefreshCancelAttendance';
    }
}
