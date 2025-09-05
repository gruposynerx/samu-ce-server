<?php

namespace App\Enums;

enum ClosingTypeEnum: int
{
    case DEATH = 1;

    case ADDRESS_NOT_FOUND = 2;

    case EVADING = 3;

    case FALSE_OCCURRENCE = 4;

    case REFUSAL_ATTENDANCE = 5;

    case REFUSAL_REMOVAL = 6;

    case REMOVED_BY_THIRD_PARTIES = 7;

    case REMOVED_BY_OTHER_VEHICLE = 8;

    public function message(): string
    {
        return match ($this) {
            self::DEATH => 'Óbito',
            self::ADDRESS_NOT_FOUND => 'Endereço não localizado',
            self::EVADING => 'Evasão do local',
            self::FALSE_OCCURRENCE => 'Falsa Ocorrência',
            self::REFUSAL_ATTENDANCE => 'Recusa atendimento',
            self::REFUSAL_REMOVAL => 'Recusa remoção',
            self::REMOVED_BY_THIRD_PARTIES => 'Removido por terceiros',
            self::REMOVED_BY_OTHER_VEHICLE => 'Remoção por outra VTR',
        };
    }
}
