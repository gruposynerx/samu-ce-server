<?php

namespace App\Enums;

enum ResourceEnum: int
{
    case BASIC_SUPPORT_UNIT = 0;

    case ADVANCED_SUPPORT_UNIT = 1;

    case AEROMEDICAL = 2;

    public function message(): string
    {
        return match ($this) {
            self::BASIC_SUPPORT_UNIT => 'USB',
            self::ADVANCED_SUPPORT_UNIT => 'USA',
            self::AEROMEDICAL => 'Aeromédico',
        };
    }
}
