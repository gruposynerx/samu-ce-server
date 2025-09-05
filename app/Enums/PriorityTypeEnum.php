<?php

namespace App\Enums;

enum PriorityTypeEnum: int
{
    case BLUE = 1;

    case GREEN = 2;

    case YELLOW = 3;

    case ORANGE = 4;

    case RED = 5;

    public function message(): string
    {
        return match ($this) {
            self::BLUE => 'Azul',
            self::GREEN => 'Verde',
            self::YELLOW => 'Amarelo',
            self::ORANGE => 'Laranja',
            self::RED => 'Vermelho',
        };
    }
}
