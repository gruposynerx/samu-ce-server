<?php

namespace App\Enums;

enum TransferReasonEnum: int
{
    case ZERO_VACANCY = 1;

    case VACANCY_RELEASED_BY_BED_CENTRAL = 2;

    case REGULATED_VACANCY = 3;

    public function message(): string
    {
        return match ($this) {
            self::ZERO_VACANCY => 'Vaga Zero',
            self::VACANCY_RELEASED_BY_BED_CENTRAL => 'Vaga liberada pela central de leitos',
            self::REGULATED_VACANCY => 'Vaga regulada',
        };
    }

    const TRANSFER_REASON_SECONDARY_ATTENDANCE = [
        self::ZERO_VACANCY,
        self::VACANCY_RELEASED_BY_BED_CENTRAL,
    ];
}
