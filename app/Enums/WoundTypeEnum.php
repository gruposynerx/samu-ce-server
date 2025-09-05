<?php

namespace App\Enums;

enum WoundTypeEnum: int
{
    case AMPUTATION_OR_AVULSION = 1;

    case CONTUSION = 2;

    case CUTTING = 3;

    case CUTTING_CONTUSION = 4;

    case EXCORIATION = 5;

    case STAB_WOUNDS = 6;

    case FIREARM_WOUNDS = 7;

    case EXPOSED_FRACTURE = 8;

    case CLOSED_FRACTURE = 9;

    case BLEEDING = 10;

    case PERFORATING = 11;

    case BURN = 12;

    case OTHERS = 13;

    public function message(): string
    {
        return match ($this) {
            self::AMPUTATION_OR_AVULSION => 'Amputação/Avulsão',
            self::CONTUSION => 'Contusão',
            self::CUTTING => 'Cortante',
            self::CUTTING_CONTUSION => 'Corto-Contuso',
            self::EXCORIATION => 'Escoriação',
            self::STAB_WOUNDS => 'FAB',
            self::FIREARM_WOUNDS => 'FAF',
            self::EXPOSED_FRACTURE => 'Fratura Exposta',
            self::CLOSED_FRACTURE => 'Fratura Fechada',
            self::BLEEDING => 'Hemorragia',
            self::PERFORATING => 'Perfurante',
            self::BURN => 'Queimadura',
            self::OTHERS => 'Outros',
        };
    }
}
