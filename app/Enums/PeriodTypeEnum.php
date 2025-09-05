<?php

namespace App\Enums;

enum PeriodTypeEnum: int
{
    case DAYTIME = 1;

    case NOCTURNAL = 2;

    public function message(): string
    {
        return match ($this) {
            self::DAYTIME => 'Diurno',
            self::NOCTURNAL => 'Noturno',
        };
    }
}
