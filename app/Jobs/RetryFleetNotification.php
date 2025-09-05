<?php

namespace App\Jobs;

use App\Enums\RadioOperationFleetStatusEnum;
use App\Models\NotificationType;
use App\Models\RadioOperationFleet;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RetryFleetNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public $timeout = 60;

    private $fleetId;

    private $notificationTypeId;

    private $action;

    private $retryCount;

    public function __construct(
        string $fleetId,
        int $notificationTypeId,
        string $action,
        int $retryCount
    ) {
        $this->fleetId = $fleetId;
        $this->notificationTypeId = $notificationTypeId;
        $this->action = $action;
        $this->retryCount = $retryCount;
    }

    public function handle(NotificationService $notificationService): void
    {
        $fleetExists = RadioOperationFleet::where('id', $this->fleetId)->exists();
        if (!$fleetExists) {
            Log::warning('RetryFleetNotification::handle - Frota não encontrada, cancelando job', [
                'fleet_id' => $this->fleetId,
                'retry_count' => $this->retryCount,
            ]);
            return;
        }

        try {
            $fleet = RadioOperationFleet::findOrFail($this->fleetId);
            $notificationType = NotificationType::findOrFail($this->notificationTypeId);

            if ($fleet->status !== RadioOperationFleetStatusEnum::AWAITING_CONFIRMATION->value) {
                return;
            }
            $users = $fleet->users()->whereNotNull('user_id')->get();

            if ($users->isEmpty()) {
                Log::warning('RetryFleetNotification::handle - Nenhum usuário encontrado na frota', [
                    'fleet_id' => $this->fleetId,
                ]);

                return;
            }

            foreach ($users as $user) {
                $notificationService->scheduleNotification(
                    $user,
                    $fleet,
                    $notificationType,
                    $this->action,
                    $this->retryCount
                );
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
