<?php

enum DayOfWeekEnum: int
{
    case SUNDAY = 0;

    case MONDAY = 1;

    case TUESDAY = 2;

    case WEDNESDAY = 3;

    case THURSDAY = 4;

    case FRIDAY = 5;

    case SATURDAY = 6;

    public function message(): string
    {
        return match ($this) {
            self::SUNDAY => 'Domingo',
            self::MONDAY => 'Segunda-feira',
            self::TUESDAY => 'Terça-feira',
            self::WEDNESDAY => 'Quarta-feira',
            self::THURSDAY => 'Quinta-feira',
            self::FRIDAY => 'Sexta-feira',
            self::SATURDAY => 'Sábado',
        };
    }
}
