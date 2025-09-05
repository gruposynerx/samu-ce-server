<?php

namespace App\Enums;

enum RespirationTypeEnum: int
{
    case DOES_NOT_BREATHE = 1;

    case NORMAL = 2;

    case NOISY_DYSPNEA = 3;

    case OBSTRUCTED_AIRWAY = 4;

    case ACCESSIBLE_AIRWAY = 5;

    public function message(): string
    {
        return match ($this) {
            self::DOES_NOT_BREATHE => 'Não Respira',
            self::NORMAL => 'Normal',
            self::NOISY_DYSPNEA => 'Ruidosa/Dispneia',
            self::OBSTRUCTED_AIRWAY => 'Via aérea obstruída',
            self::ACCESSIBLE_AIRWAY => 'Via aérea pérvia',
        };
    }
}
