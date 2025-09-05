<?php

namespace App\Enums;

enum ConsciousnessLevelEnum: int
{
    case NORMAL = 1;

    case CONFUSED = 2;

    case TORPOROUS = 3;

    case UNCONSCIOUS = 4;

    public function message(): string
    {
        return match ($this) {
            self::NORMAL => 'Normal',
            self::CONFUSED => 'Confuso',
            self::TORPOROUS => 'Torporoso',
            self::UNCONSCIOUS => 'Inconsciente',
        };
    }
}
