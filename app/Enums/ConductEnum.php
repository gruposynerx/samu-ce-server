<?php

namespace App\Enums;

enum ConductEnum: int
{
    case MEDICAL_REGULATOR = 1;

    case MEDICAL_INTERVENTIONAL = 2;

    case NURSING_TEAM = 3;

    public function message(): string
    {
        return match ($this) {
            self::MEDICAL_REGULATOR => 'Médico Regulador',
            self::MEDICAL_INTERVENTIONAL => 'Médico Intervencionista',
            self::NURSING_TEAM => 'Equipe de enfermagem',
        };
    }
}
