<?php

namespace App\Enums;

enum BaseTypeEnum: int
{
    case MOBILE_PRE_HOSPITAL_EMERGENCY_UNIT = 42;

    public function message(): string
    {
        return match ($this) {
            self::MOBILE_PRE_HOSPITAL_EMERGENCY_UNIT => 'UNIDADE MÓVEL DE NÍVEL PRE-HOSPITALAR NA AREA DE URGÊNCIA',
        };
    }
}
