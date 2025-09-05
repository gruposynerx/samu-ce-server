<?php

namespace App\Enums;

enum UserStatusEnum: int
{
    case ACTIVE = 1;

    case INACTIVE = 2;

    public function message(): string
    {
        return match ($this) {
            self::ACTIVE => 'Ativo',
            self::INACTIVE => 'Inativo',
        };
    }
}
