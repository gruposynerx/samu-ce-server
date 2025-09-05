<?php

namespace App\Jobs;

use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ChangeAttendanceStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $fleetId;

    public function __construct(string $fleetId)
    {
        $this->fleetId = $fleetId;
    }

    public function handle(NotificationService $notificationService)
    {
        $notificationService->handleNoResponse($this->fleetId);
    }
}
