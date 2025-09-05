<?php

namespace App\Enums;

enum RolesEnum: string
{
    case SUPER_ADMIN = 'super-admin';
    case ADMIN = 'admin';
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

    public const ALLLOWED_ROLES_TO_CLOSE_ATTENDANCE = [
        self::SUPER_ADMIN,
        self::ADMIN,
        self::TEAM_LEADER,
    ];
}
