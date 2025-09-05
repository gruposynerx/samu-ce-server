<?php

namespace App\Enums;

enum VehicleTypeEnum: int
{
    case TRANSPORT_AMBULANCE = 1;

    case BASIC_SUPPORT_UNIT = 2;

    case ADVANCED_SUPPORT_UNIT = 3;

    case EMBARCATION = 4;

    case AEROMEDICAL = 5;

    case RAPID_INTERVENTION_VEHICLE = 6;

    case MOTORCYCLE_AMBULANCE = 7;

    public function message(): string
    {
        return match ($this) {
            self::TRANSPORT_AMBULANCE => 'Ambulância de Transporte',
            self::BASIC_SUPPORT_UNIT => 'USB',
            self::ADVANCED_SUPPORT_UNIT => 'USA',
            self::AEROMEDICAL => 'Aeromédico',
            self::RAPID_INTERVENTION_VEHICLE => 'VIR',
            self::MOTORCYCLE_AMBULANCE => 'Motolância',
            self::EMBARCATION => 'Embarcação',
        };
    }
}
