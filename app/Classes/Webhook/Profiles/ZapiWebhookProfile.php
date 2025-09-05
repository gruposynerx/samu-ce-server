<?php

namespace App\Classes\Webhook\Profiles;

use App\Entities\Zapi\WebhookReceipt\WebhookReceiptResponse;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Spatie\WebhookClient\WebhookProfile\WebhookProfile;

class ZapiWebhookProfile implements WebhookProfile
{
    public function shouldProcess(Request $request): bool
    {
        $webhookCall = new WebhookReceiptResponse($request->all());

        if ($webhookCall->buttonReply) {
            $referenceMessageId = $webhookCall->buttonReply?->referenceMessageId ?? $webhookCall->referenceMessageId;

            $ticket = Ticket::withoutGlobalScopes()->where('message_id', $referenceMessageId)->first();

            return $ticket && $ticket->receive_notification;
        }

        return false;
    }
}
