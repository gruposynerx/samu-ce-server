<?php

namespace App\Enums;

enum RequesterTypeEnum: int
{
    case MEDICAL = 1;

    case OTHER_PROFESSIONAL = 2;

    case HIMSELF = 3;

    case FAMILY = 4;

    case FRIEND = 5;

    case PASSERBY = 6;

    case HEALTH_PROFESSIONAL = 7;

    case MUNICIPAL_GUARD = 8;

    case FIREFIGHTERS = 9;

    case MILITARY_POLICE = 10;

    case STATE_HIGHWAY_POLICE = 11;

    case FEDERAL_HIGHWAY_POLICE = 12;

    case MUNICIPAL_AUTHORITY = 13;

    public function message(): string
    {
        return match ($this) {
            self::MEDICAL => 'Médico',
            self::OTHER_PROFESSIONAL => 'Outro Profissional',
            self::HIMSELF => 'O Próprio',
            self::FAMILY => 'Familiar',
            self::FRIEND => 'Amigo',
            self::PASSERBY => 'Transeunte',
            self::HEALTH_PROFESSIONAL => 'Prof. Saúde',
            self::MUNICIPAL_GUARD => 'Guarda Municipal',
            self::FIREFIGHTERS => 'Bombeiros',
            self::MILITARY_POLICE => 'PM',
            self::STATE_HIGHWAY_POLICE => 'PRE',
            self::FEDERAL_HIGHWAY_POLICE => 'PRF',
            self::MUNICIPAL_AUTHORITY => 'AMC',
        };
    }

    const REQUESTER_SECONDARY_ATTENDANCE = [
        self::MEDICAL,
        self::OTHER_PROFESSIONAL,
    ];
}
