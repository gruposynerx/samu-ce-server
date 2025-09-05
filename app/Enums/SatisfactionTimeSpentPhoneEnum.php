<?php

namespace App\Enums;

enum SatisfactionTimeSpentPhoneEnum: int
{
    case FAST = 1;

    case AS_EXPECTED = 2;

    case LENGTHY = 3;

    case WAS_NOT_WHO_CALLED = 4;

    case THERE_WAS_NO_CALL = 5;

    public function message(): string
    {
        return match ($this) {
            self::FAST => 'Rápido',
            self::AS_EXPECTED => 'Dentro do esperado',
            self::LENGTHY => 'Demorado',
            self::WAS_NOT_WHO_CALLED => 'Não fui eu quem fez a ligação para o telefone 192',
            self::THERE_WAS_NO_CALL => 'Não houve ligação para o telefone 192',
        };
    }
}
