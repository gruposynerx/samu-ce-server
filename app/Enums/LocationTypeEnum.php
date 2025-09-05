<?php

namespace App\Enums;

enum LocationTypeEnum: int
{
    case PUBLIC_ROAD = 1;

    case RESIDENCE = 2;

    case SCHOOL = 3;

    case COMPANY = 4;

    case REST_HOME = 5;

    case EMERGENCY_CARE_UNIT = 6;

    case HOSPITAL = 7;

    case BASIC_SUPPORT_UNIT = 8;

    case RURAL_AREA = 9;

    case POLICE_STATION_OR_PRISON = 10;

    case OTHERS = 11;

    case HIGHWAY = 12;

    public function message(): string
    {
        return match ($this) {
            self::PUBLIC_ROAD => 'Via Pública',
            self::RESIDENCE => 'Residência',
            self::SCHOOL => 'Escola',
            self::COMPANY => 'Empresa',
            self::REST_HOME => 'Casa de Repouso',
            self::EMERGENCY_CARE_UNIT => 'UPA',
            self::HOSPITAL => 'Hospital',
            self::BASIC_SUPPORT_UNIT => 'UBS',
            self::RURAL_AREA => 'Zona Rural',
            self::POLICE_STATION_OR_PRISON => 'Delegacia/Presídio',
            self::HIGHWAY => 'Rodovia',
            self::OTHERS => 'Outros',
        };
    }
}
