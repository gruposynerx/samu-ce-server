<?php

namespace App\Enums;

enum VehicleMovementCodeEnum: int
{
    case CODE_1 = 1;

    case CODE_2 = 2;

    case CODE_3 = 3;

    public function message(): string
    {
        return match ($this) {
            self::CODE_1 => 'Código 1',
            self::CODE_2 => 'Código 2',
            self::CODE_3 => 'Código 3',
        };
    }
}
