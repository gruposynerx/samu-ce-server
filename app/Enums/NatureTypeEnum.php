<?php

namespace App\Enums;

enum NatureTypeEnum: int
{
    case TRAUMA = 1;

    case CLINICAL = 2;

    case GYNECO_OBSTETRIC = 3;

    case PEDIATRIC = 4;

    case PSYCHIATRIC = 5;

    case NEONATAL = 6;

    case EVENT = 7;

    public function message(): string
    {
        return match ($this) {
            self::TRAUMA => 'Trauma',
            self::CLINICAL => 'Clínico',
            self::GYNECO_OBSTETRIC => 'Gineco Obstétrico',
            self::PEDIATRIC => 'Pediátrico',
            self::PSYCHIATRIC => 'Psiquiátrico',
            self::NEONATAL => 'Neonatal',
            self::EVENT => 'Evento',
        };
    }
}
