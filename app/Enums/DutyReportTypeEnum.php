<?php

namespace App\Enums;

enum DutyReportTypeEnum: int
{
    case TEAM_LEADER = 1;

    case FLEET_MANAGER = 2;

    public function message(): string
    {
        return match ($this) {
            self::TEAM_LEADER => 'Chefe de equipe',
            self::FLEET_MANAGER => 'Gerente de frota',
        };
    }
}
