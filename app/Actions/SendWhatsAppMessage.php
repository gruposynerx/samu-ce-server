<?php

namespace App\Actions;

use App\Contracts\WhatsAppMessageApi;
use App\Enums\AttendanceStatusEnum;
use App\Models\Attendance;

class SendWhatsAppMessage extends Actionable
{
    public function __construct(private readonly WhatsAppMessageApi $api)
    {
    }

    private function formatAddress(Attendance $attendance): string
    {
        $attendable = $attendance->attendable;

        $street = $attendable->street;
        $houseNumber = $attendable->house_number;
        $neighborhood = $attendable->neighborhood;
        $city = $attendance->ticket->city->name;
        $referencePlace = $attendable->reference_place;

        return "_{$street}, {$houseNumber}, {$neighborhood} - {$city} - {$referencePlace}";
    }

    public function handle(Attendance $attendance = null): void
    {
        $this->validateParams(['attendance'], compact('attendance'));

        $attendance->loadMissing(['ticket.requester', 'ticket.city', 'attendable']);

        $attendanceStatusId = $attendance->attendance_status_id instanceof AttendanceStatusEnum
            ? $attendance->attendance_status_id
            : AttendanceStatusEnum::tryFrom($attendance->attendance_status_id);

        $isNotifiableStatus = in_array($attendanceStatusId, AttendanceStatusEnum::ALL_WHATSAPP_NOTIFIABLE_STATUSES);

        if ($attendance->ticket->receive_notification && $isNotifiableStatus) {
            $messageIdentifier = $attendanceStatusId->messageIdentifier();

            $message = __("whatsapp_messages.ticket_infos.{$messageIdentifier}", [
                'user' => $attendance->ticket->requester->name,
                'protocol' => $attendance->number,
                'address' => $this->formatAddress($attendance),
            ]);

            $this->api->setPhones($attendance->ticket->requester->primary_phone);
            $this->api->sendTextMessage(implode("\n", $message));
        }
    }
}
