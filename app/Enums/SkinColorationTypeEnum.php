<?php

namespace App\Enums;

enum SkinColorationTypeEnum: int
{
    case CYANOSIS = 1;

    case NORMAL = 2;

    case PALLOR = 3;

    public function message(): string
    {
        return match ($this) {
            self::CYANOSIS => 'Cianose',
            self::NORMAL => 'Normal',
            self::PALLOR => 'Palidez',
        };
    }
}
