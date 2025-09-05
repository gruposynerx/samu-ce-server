<?php

namespace App\Enums;

enum BleedingTypeEnum: int
{
    case EXTERNAL_ACTIVE = 1;

    case CONTROLLED = 2;

    case SUSPECTED_INTERNAL_BLEEDING = 3;

    case NO_SUGGESTIVE_SIGNS = 4;

    public function message(): string
    {
        return match ($this) {
            self::EXTERNAL_ACTIVE => 'Externo Ativo',
            self::CONTROLLED => 'Controlado',
            self::SUSPECTED_INTERNAL_BLEEDING => 'Suspeito de Sangramento Interno',
            self::NO_SUGGESTIVE_SIGNS => 'Sem sinais sugestivos',
        };
    }
}
