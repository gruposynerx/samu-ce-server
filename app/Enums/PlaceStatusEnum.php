<?php

namespace App\Enums;

enum PlaceStatusEnum: int
{
    case FREE = 1;

    case OCCUPIED = 2;

    case DISABLED = 3;

    public function message(): string
    {
        return match ($this) {
            self::FREE => 'Livre',
            self::OCCUPIED => 'Ocupado',
            self::DISABLED => 'Desativado',
        };
    }
}
