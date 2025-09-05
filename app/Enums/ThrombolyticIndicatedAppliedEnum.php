<?php

namespace App\Enums;

enum ThrombolyticIndicatedAppliedEnum: int
{
    case APPLIED = 2;

    case NOT_APPLIED = 1;

    public function message(): string
    {
        return match ($this) {
            self::APPLIED => 'Aplicado',
            self::NOT_APPLIED => 'Não aplicado',
        };
    }
}
