<?php

namespace App\Enums;

enum UserLogLoginTypeEnum: int
{
    case COMPLETED_SUCCESSFULLY = 1;

    case BLOCKED_BY_MOBILE_ACCESS_NOT_ALLOWED = 2;

    public function message(): string
    {
        return match ($this) {
            self::COMPLETED_SUCCESSFULLY => 'Concluído com sucesso',
            self::BLOCKED_BY_MOBILE_ACCESS_NOT_ALLOWED => 'Bloqueado por acesso não permitido por dispositivo móvel',
        };
    }
}
