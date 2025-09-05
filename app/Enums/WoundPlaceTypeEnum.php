<?php

namespace App\Enums;

enum WoundPlaceTypeEnum: int
{
    case ABDOMEN = 1;

    case FOREARM = 2;

    case MOUTH_TONGUE = 3;

    case ARM = 4;

    case HEAD = 5;

    case THIGH = 6;

    case HAND_FINGERS = 7;

    case FOOT_FINGERS = 8;

    case BACK = 9;

    case FACE = 10;

    case KNEE = 11;

    case HAND = 12;

    case EYE = 13;

    case SHOULDER = 14;

    case FOOT = 15;

    case PELVIS_BASIN = 16;

    case PERINEUM = 17;

    case LEG = 18;

    case NECK = 19;

    case CHEST = 20;

    public function message(): string
    {
        return match ($this) {
            self::ABDOMEN => 'Abdome',
            self::FOREARM => 'Antebraço',
            self::MOUTH_TONGUE => 'Boca/Língua',
            self::ARM => 'Braço',
            self::HEAD => 'Cabeça',
            self::THIGH => 'Coxa',
            self::HAND_FINGERS => 'Dedos da Mão',
            self::FOOT_FINGERS => 'Dedos do Pé',
            self::BACK => 'Dorso',
            self::FACE => 'Face',
            self::KNEE => 'Joelho',
            self::HAND => 'Mão',
            self::EYE => 'Olho',
            self::SHOULDER => 'Ombro',
            self::FOOT => 'Pé',
            self::PELVIS_BASIN => 'Pelve/Bacia',
            self::PERINEUM => 'Períneo',
            self::LEG => 'Perna',
            self::NECK => 'Pescoço',
            self::CHEST => 'Tórax',
        };
    }
}
