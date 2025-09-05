<?php

namespace App\Enums;

enum DistanceTypeEnum: int
{
    case WITH_PATIENT = 1;

    case NEAR = 2;

    case AWAY = 3;

    case NOT_WITH_PATIENT = 4;

    public function message(): string
    {
        return match ($this) {
            self::WITH_PATIENT => 'Com o Paciente',
            self::NEAR => 'Próximo',
            self::AWAY => 'Longe',
            self::NOT_WITH_PATIENT => 'Não está com o paciente',
        };
    }
}
