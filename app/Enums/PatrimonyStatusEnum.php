<?php

namespace App\Enums;

enum PatrimonyStatusEnum: int
{
    case AVAILABLE = 1;
    case UNAVAILABLE = 2;
    case RETAINED = 3;

    public function message(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Disponível',
            self::UNAVAILABLE => 'Indisponível',
            self::RETAINED => 'Retido',
        };
    }
}
