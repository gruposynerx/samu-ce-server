<?php

namespace App\Enums;

enum NotificationTypeEnum: string
{
    case FLEET_ASSIGNMENT = 'fleet_assignment';
    case FLEET_ASSIGNMENT_REMINDER = 'fleet_assignment_reminder';

    public function description(): string
    {
        return match ($this) {
            self::FLEET_ASSIGNMENT => 'Notificação de designação de frota para atendimento',
            self::FLEET_ASSIGNMENT_REMINDER => 'Lembrete: designação de frota para atendimento',
        };
    }

    public function name(): string
    {
        return $this->value;
    }
}
