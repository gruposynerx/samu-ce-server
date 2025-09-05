<?php

namespace App\Services;

use App\Contracts\WhatsAppMessageApi;
use App\Entities\Zapi\OptionsList;
use App\Models\Attendance;
use App\Models\Ticket;

class AttendanceMonitoringService
{
    public function __construct()
    {
        $this->zapi = app(WhatsAppMessageApi::class);
    }

    public function linkAttendance(array $requesterParameters, array $messageParameters): void
    {
        $this->zapi->setPhones($requesterParameters['phones']);

        $keysAndValues = [];
        $formattedMessageDescription = [];

        foreach ($messageParameters as $messageParameter) {
            $keysAndValues[] = [
                '#:protocol' => $messageParameter['protocol'],
                ':link' => $messageParameter['link'],
            ];
        }

        $formattedMessageHeadline = __('whatsapp_messages.ticket_infos.request_made.headline', [
            'user' => $requesterParameters['name'],
        ]);

        foreach ($keysAndValues as $keyAndValue) {
            $formattedMessageDescription[] = __('whatsapp_messages.ticket_infos.request_made.description', [
                'protocol' => $keyAndValue['#:protocol'],
            ]);
        }

        $descriptionString = implode("\n", $formattedMessageDescription);

        $messageFooter = __('whatsapp_messages.ticket_infos.request_made.footer');

        $formattedFinalMessage = "$formattedMessageHeadline $descriptionString $messageFooter";

        $buttonsList = new OptionsList(__('whatsapp_messages.ticket_infos.request_made.buttons_list', [
            'url' => $messageParameters[0]['link'],
        ]));

        $messageId = $this->zapi->sendOptionButtons($formattedFinalMessage, $buttonsList)->first()?->id;

        if ($messageId) {
            $ticket = Attendance::with('ticket')->select('id', 'ticket_id')->findOrFail($messageParameters[0]['attendance_id'])->ticket;
            $ticket->update(['message_id' => $messageId]);
        }
    }

    public function stopMonitoring(int|Ticket $ticket, ?string $phone): void
    {
        $ticket = $ticket instanceof Ticket ? $ticket : Ticket::withoutGlobalScopes()->findOrFail($ticket);

        $phone = $phone ?? $ticket->phone;

        $ticket->update(['receive_notification' => false]);

        $this->zapi->setPhones($phone)->sendTextMessage('Tudo bem, não enviaremos mais alertas sobre esta ocorrência 👍');
    }

    public function startMonitoring(int|Ticket $ticket, ?string $phone): void
    {
        $ticket = $ticket instanceof Ticket ? $ticket : Ticket::withoutGlobalScopes()->findOrFail($ticket);

        $phone = $phone ?? $ticket->phone;

        $ticket->update(['receive_notification' => true]);

        $this->zapi->setPhones($phone)->sendTextMessage('Pode deixar! Vamos avisar você sobre novas atualizações desta ocorrência 🧑‍⚕️👍');
    }
}
