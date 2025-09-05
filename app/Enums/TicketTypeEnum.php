<?php

namespace App\Enums;

enum TicketTypeEnum: int
{
    case PRIMARY_OCCURRENCE = 1;

    case SECONDARY_OCCURRENCE = 2;

    case PRANK_CALL = 3;

    case INFORMATION = 4;

    case MISTAKE = 5;

    case CALL_DROP = 6;

    case CONTACT_WITH_SAMU_TEAM = 7;

    public function message(): string
    {
        return match ($this) {
            self::PRIMARY_OCCURRENCE => 'Ocorrência Primária',
            self::SECONDARY_OCCURRENCE => 'Ocorrência Secundária',
            self::PRANK_CALL => 'Trote',
            self::INFORMATION => 'Informação',
            self::MISTAKE => 'Engano',
            self::CALL_DROP => 'Queda da Ligação',
            self::CONTACT_WITH_SAMU_TEAM => 'Contato com Equipe SAMU',
        };
    }

    const OTHER_ATTENDANCES = [
        self::INFORMATION,
        self::MISTAKE,
        self::PRANK_CALL,
        self::CALL_DROP,
        self::CONTACT_WITH_SAMU_TEAM,
    ];
}
