<?php

namespace App\Jobs;

use App\Entities\Zapi\WebhookReceipt\WebhookReceiptResponse;
use App\Models\Ticket;
use App\Services\AttendanceMonitoringService;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob as SpatieProcessWebhookJob;

class ProcessMessageReceipt extends SpatieProcessWebhookJob
{
    public function handle(AttendanceMonitoringService $attendanceMonitoringService): void
    {
        $payload = new WebhookReceiptResponse($this->webhookCall->payload);

        $ticket = Ticket::withoutGlobalScopes()
            ->where('message_id', $payload->buttonReply->referenceMessageId)
            ->first();

        switch ($payload->buttonReply->buttonId) {
            case 2:
                $attendanceMonitoringService->startMonitoring($ticket, $payload->phone);
                break;
            case 3:
                $attendanceMonitoringService->stopMonitoring($ticket, $payload->phone);
                break;
        }
    }
}
