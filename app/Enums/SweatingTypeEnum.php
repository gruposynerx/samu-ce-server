<?php

namespace App\Enums;

enum SweatingTypeEnum: int
{
    case CHANGED = 1;

    case NORMAL = 2;

    public function message(): string
    {
        return match ($this) {
            self::CHANGED => 'Alterada',
            self::NORMAL => 'Normal',
        };
    }
}
