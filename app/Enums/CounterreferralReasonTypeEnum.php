<?php

namespace App\Enums;

enum CounterreferralReasonTypeEnum: int
{
    case OUT_OF_PROFILE = 1;

    case BED_UNAVAILABLE = 2;

    case UNAVAILABILITY_OF_NECESSARY_RESOURCES = 3;

    case THE_ON_CALL_DOCTORS_REFUSAL = 4;

    case OTHERS = 5;

    public function message(): string
    {
        return match ($this) {
            self::OUT_OF_PROFILE => 'Fora do perfil',
            self::BED_UNAVAILABLE => 'Indisponibilidade de leitos',
            self::UNAVAILABILITY_OF_NECESSARY_RESOURCES => 'Indisponibilidade de recursos necessários',
            self::THE_ON_CALL_DOCTORS_REFUSAL => 'Negativa do plantonista',
            self::OTHERS => 'Outros',
        };
    }
}
