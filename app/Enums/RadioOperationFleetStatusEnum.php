<?php

namespace App\Enums;

enum RadioOperationFleetStatusEnum: int
{
    case AWAITING_CONFIRMATION = 0;

    case CONFIRMED = 1;

    case NO_RESPONSE = 2;

    case MANUAL_REGISTRATION_NO_APP = 3;

    public function message(): string
    {
        return match ($this) {
            self::AWAITING_CONFIRMATION => 'Aguardar Confirmação',
            self::CONFIRMED => 'Confirmar Equipe VTR',
            self::NO_RESPONSE => 'Sem Resposta da VTR',
            self::MANUAL_REGISTRATION_NO_APP => 'Registro manual (Sem App)',
        };
    }

    public static function getMessage(int $value): string
    {
        return self::tryFrom($value)->message();
    }
}
