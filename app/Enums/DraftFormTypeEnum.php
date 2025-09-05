<?php

namespace App\Enums;

enum DraftFormTypeEnum: string
{
    case PRIMARY_ATTENDANCE = 'primary_attendance';

    case SECONDARY_ATTENDANCE = 'secondary_attendance';

    public function message(): string
    {
        return match ($this) {
            self::PRIMARY_ATTENDANCE => 'Ocorrencia Primária',
            self::SECONDARY_ATTENDANCE => 'Ocorrencia Secundária',
        };
    }
}
