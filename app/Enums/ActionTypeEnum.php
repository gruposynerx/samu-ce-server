<?php

namespace App\Enums;

enum ActionTypeEnum: int
{
    case WITH_INTERVENTION = 1;

    case WITHOUT_INTERVENTION = 2;

    case WAITING_FOR_RETURN = 3;

    case WAITING_FOR_VACANCY = 4;

    public function message(): string
    {
        return match ($this) {
            self::WITH_INTERVENTION => 'Com intervenção',
            self::WITHOUT_INTERVENTION => 'Sem intervenção',
            self::WAITING_FOR_RETURN => 'Aguardando retorno',
            self::WAITING_FOR_VACANCY => 'Aguardando vaga',
        };
    }
}
