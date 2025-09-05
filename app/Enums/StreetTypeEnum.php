<?php

namespace App\Enums;

enum StreetTypeEnum: int
{
    case STREET = 0;

    case AVENUE = 1;

    case PLATTER = 2;

    case ROAD = 3;

    case TREE_LINED_AVENUE = 4;

    public function message(): string
    {
        return match ($this) {
            self::STREET => 'Rua',
            self::AVENUE => 'Avenida',
            self::PLATTER => 'Travessa',
            self::ROAD => 'Rodovia',
            self::TREE_LINED_AVENUE => 'Alameda',
        };
    }
}
