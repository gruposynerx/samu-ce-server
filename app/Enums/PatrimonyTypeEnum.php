<?php

namespace App\Enums;

enum PatrimonyTypeEnum: int
{
    case BED = 1;
    case SIEMENS_CELL_PHONE_WITH_CHARGER = 2;
    case PORTABLE_VACUUM_CLEANER = 3;
    case OXYGEN_FLOWMETER = 4;
    case CARDIAC_DEFIBRILLATOR_MONITOR = 5;
    case SHORT_PLANK_WITH_STRAPS = 6;
    case LONG_BOARD_WITH_STRAPS = 7;
    case HUMIDIFIERS = 8;
    case MECHANICAL_OR_CIRCUIT_FAN = 9;
    case VACUUM_CLEANER_GLASS_WITH_LID = 10;
    case CELL_PHONE_WITH_CHARGER = 11;
    case AUTOMATIC_EXTERNAL_DEFIBRILLATOR = 12;
    case OXIMETER_CHARGER = 13;

    public function message(): string
    {
        return match ($this) {
            self::BED => 'Maca',
            self::SIEMENS_CELL_PHONE_WITH_CHARGER => 'Aparelho Celular Siemens C/ Carregador',
            self::PORTABLE_VACUUM_CLEANER => 'Aspirador Portátil',
            self::OXYGEN_FLOWMETER => 'Fluxometro de Oxigênio',
            self::CARDIAC_DEFIBRILLATOR_MONITOR => 'Monitor Desf. Cardíaco',
            self::SHORT_PLANK_WITH_STRAPS => 'Prancha Curta com Tirantes',
            self::LONG_BOARD_WITH_STRAPS => 'Prancha Longa com Tirantes',
            self::HUMIDIFIERS => 'Umidificadores',
            self::MECHANICAL_OR_CIRCUIT_FAN => 'Ventilador Mecânico / Circuito',
            self::VACUUM_CLEANER_GLASS_WITH_LID => 'Vidro de Aspirador C/ Tampa',
            self::CELL_PHONE_WITH_CHARGER => 'Aparelho Celular com Carregador',
            self::AUTOMATIC_EXTERNAL_DEFIBRILLATOR => 'DEA (Desfibrilador Externo Automático)',
            self::OXIMETER_CHARGER => 'Carregador de Oxímetro',
        };
    }
}
