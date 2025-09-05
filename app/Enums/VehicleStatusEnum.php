<?php

namespace App\Enums;

enum VehicleStatusEnum: int
{
    case ACTIVE = 1;

    case IN_MAINTENANCE = 2;

    case UNAVAILABLE = 3;

    case COMMITTED = 4;

    case SOLICITED = 5;

    case INACTIVE = 6;

    public function message(): string
    {
        return match ($this) {
            self::ACTIVE => 'Ativa',
            self::IN_MAINTENANCE => 'Em manutenção',
            self::UNAVAILABLE => 'Indisponível',
            self::COMMITTED => 'Empenhada',
            self::SOLICITED => 'Solicitada',
            self::INACTIVE => 'Baixada',
        };
    }

    public const MANUAL_STATUSES = [
        self::ACTIVE,
        self::IN_MAINTENANCE,
        self::UNAVAILABLE,
        self::SOLICITED,
        self::INACTIVE,
    ];

    public const ABLE_TO_OPERATION = [
        self::ACTIVE,
        self::SOLICITED,
        self::COMMITTED,
    ];
}
