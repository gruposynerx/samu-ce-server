<?php

namespace App\Enums;

enum AntecedentTypeEnum: int
{
    case HEART_DISEASE = 1;

    case DIABETES = 2;

    case EPILEPSY = 3;

    case ALCOHOLISM = 4;

    case HYPERTENSION = 5;

    case HIV = 6;

    case NEPHROPATHY = 7;

    case NEOPLASM = 8;

    case PNEUMOPATHY = 9;

    case STROKE_SEQUEL = 10;

    public function message(): string
    {
        return match ($this) {
            self::HEART_DISEASE => 'Cardiopatia',
            self::DIABETES => 'Diabetes',
            self::EPILEPSY => 'Epilepsia',
            self::ALCOHOLISM => 'Etilismo',
            self::HYPERTENSION => 'Hipertensão',
            self::HIV => 'HIV',
            self::NEPHROPATHY => 'Nefropatia',
            self::NEOPLASM => 'Neoplasia',
            self::PNEUMOPATHY => 'Pneumopatia',
            self::STROKE_SEQUEL => 'Sequela AVC',
        };
    }
}
