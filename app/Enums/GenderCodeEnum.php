<?php

namespace App\Enums;

enum GenderCodeEnum: string
{
    case MASCULINE = 'M';

    case FEMININE = 'F';

    case OTHER = 'O';

    public function message(): string
    {
        return match ($this) {
            self::MASCULINE => 'Masculino',
            self::FEMININE => 'Feminino',
            self::OTHER => 'Outro',
        };
    }
}
