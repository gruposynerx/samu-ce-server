<?php

namespace App\Enums;

enum TimeUnitEnum: int
{
    case DAYS = 1;

    case MONTHS = 2;

    case YEARS = 3;

    public function message(): string
    {
        return match ($this) {
            self::DAYS => 'Dia(s)',
            self::MONTHS => 'Mês(es)',
            self::YEARS => 'Ano(s)',
        };
    }
}
