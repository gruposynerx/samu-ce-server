<?php

namespace App\Enums;

enum UserRoleEnum: string
{
    case ADMIN = 'admin';

    case SUPER_ADMIN = 'super-admin';

    case TARM = 'TARM';

    case MEDIC = 'medic';

    case RADIO_OPERATOR = 'radio-operator';

    case ATTENDANCE_OR_AMBULANCE_TEAM = 'attendance-or-ambulance-team';

    case TEAM_SCALE = 'team-scale';

    case HOSPITAL = 'hospital';

    case SUPPORT = 'support';

    case REPORTS_CONSULTING = 'reports-consulting';

    case MONITOR = 'monitor';

    case FLEET_CONTROL = 'fleet-control';

    case TEAM_LEADER = 'team-leader';

    case MANAGER = 'manager';

    case COORDINATOR = 'coordinator';

    case SCHEDULE_MANAGER = 'schedule-manager';

    public function message(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::SUPER_ADMIN => 'Super administrador',
            self::TARM => 'TARM',
            self::MEDIC => 'Médico',
            self::RADIO_OPERATOR => 'Rádio Operador',
            self::ATTENDANCE_OR_AMBULANCE_TEAM => 'Equipe de atendimento / ambulância',
            self::TEAM_SCALE => 'Escala de equipe',
            self::HOSPITAL => 'Hospital',
            self::SUPPORT => 'Apoio',
            self::REPORTS_CONSULTING => 'Consultas de relatórios',
            self::MONITOR => 'Monitor',
            self::FLEET_CONTROL => 'Controle de frota',
            self::TEAM_LEADER => 'Chefe de equipe',
            self::MANAGER => 'Gestor',
            self::COORDINATOR => 'Gerente',
            self::SCHEDULE_MANAGER => 'Gestor de escalas'
        };
    }
}
