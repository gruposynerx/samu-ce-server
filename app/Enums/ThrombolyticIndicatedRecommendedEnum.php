<?php

namespace App\Enums;

enum ThrombolyticIndicatedRecommendedEnum: int
{
    case RECOMMENDED = 2;

    case NOT_RECOMMENDED = 1;

    public function message(): string
    {
        return match ($this) {
            self::RECOMMENDED => 'Recomemndado',
            self::NOT_RECOMMENDED => 'Não recomendado',
        };
    }
}
