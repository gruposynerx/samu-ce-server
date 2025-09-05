<?php

namespace App\Enums;

enum LinkEventScheduleEnum: string
{
    case COOPERATED = 'cooperated';
    case OUTSOURCED = 'outsourced';
    case SERVER = 'server';

    public function message(): string
    {
        return match ($this) {
            self::COOPERATED => 'Cooperado',
            self::OUTSOURCED => 'Terceirizado',
            self::SERVER => 'Servidor',
        };
    }
}
