<?php

namespace App\Enums;

enum ScheduleTypeEnum: int
{
    case DEFAULT = 1;

    case CYCLIC = 2;

    public function message(): string
    {
        return match ($this) {
            self::DEFAULT => 'Padrão',
            self::CYCLIC => 'Cíclica',
        };
    }
}
