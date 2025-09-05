<?php

namespace App\Enums;

enum SatisfactionTimeAmbulanceArriveEnum: int
{
    case FAST = 1;

    case AS_EXPECTED = 2;

    case LENGTHY = 3;

    case NOT_THERE_WHEN_CAR_ARRIVED = 4;

    case NO_EMERGENCY_SERVICES = 5;

    public function message(): string
    {
        return match ($this) {
            self::FAST => 'Rápido',
            self::AS_EXPECTED => 'Dentro do esperado',
            self::LENGTHY => 'Demorado',
            self::NOT_THERE_WHEN_CAR_ARRIVED => 'Não estava no local quando a viatura chegou',
            self::NO_EMERGENCY_SERVICES => 'Não houve atendimento de emergência',
        };
    }
}
