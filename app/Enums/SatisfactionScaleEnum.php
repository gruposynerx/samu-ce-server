<?php

namespace App\Enums;

enum SatisfactionScaleEnum: int
{
    case VERY_DISSATISFIED = 1;

    case DISSATISFIED = 2;

    case NEUTRAL = 3;

    case SATISFIED = 4;

    case VERY_SATISFIED = 5;

    public function message(): string
    {
        return match ($this) {
            self::VERY_DISSATISFIED => 'Muito insatisfeito',
            self::DISSATISFIED => 'Insatisfeito',
            self::NEUTRAL => 'Neutro',
            self::SATISFIED => 'Satisfeito',
            self::VERY_SATISFIED => 'Muito satisfeito',
        };
    }
}
