<?php

namespace App\Jobs;

use App\Models\RadioOperationFleet;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MarkFleetNoResponseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $fleetId;

    public function __construct(string $fleetId)
    {
        $this->fleetId = $fleetId;
    }

    public function handle(NotificationService $notificationService): void
    {
        $fleetExists = RadioOperationFleet::where('id', $this->fleetId)->exists();
        if (!$fleetExists) {
            Log::warning('MarkFleetNoResponseJob::handle - Frota não encontrada, cancelando job', [
                'fleet_id' => $this->fleetId,
            ]);
            return;
        }

        try {
            $notificationService->handleNoResponse($this->fleetId);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
