<?php

namespace App\Enums;

enum RadioOperationEventTypeEnum: string
{
    case VEHICLE_REQUESTED = 'vehicle_requested_at';
    case VEHICLE_DISPATCHED = 'vehicle_dispatched_at';
    case VEHICLE_RELEASED = 'vehicle_released_at';
    case ARRIVED_TO_SITE = 'arrived_to_site_at';
    case LEFT_FROM_SITE = 'left_from_site_at';
    case ARRIVED_TO_DESTINATION = 'arrived_to_destination_at';
    case RELEASE_FROM_DESTINATION = 'release_from_destination_at';
    case VEHICLE_CONFIRMED = 'vehicle_confirmed_at';

    public static function getAllEventTypes(): array
    {
        return array_column(self::cases(), 'value');
    }
}
