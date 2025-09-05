<?php

namespace App\Enums;

enum AttendanceStatusEnum: int
{
    case AWAITING_MEDICAL_REGULATION = 1;

    case AWAITING_VEHICLE_COMMITMENT = 2;

    case VEHICLE_SENT = 3;

    case AWAITING_VACANCY = 4;

    case AWAITING_CONDUCT = 5;

    case CONDUCT = 6;

    case COMPLETED = 7;

    case CANCELED = 8;

    case IN_ATTENDANCE_MEDICAL_REGULATION = 9;

    case IN_ATTENDANCE_SCENE_RECORD = 10;

    case IN_ATTENDANCE_RADIO_OPERATION = 11;

    case AWAITING_RETURN = 12;

    case LINKED = 13;

    case NO_VEHICLE_RESPONSE = 14;

    public function message(): string
    {
        return match ($this) {
            self::AWAITING_MEDICAL_REGULATION => 'Ag. Reg',
            self::AWAITING_VEHICLE_COMMITMENT => 'Ag. VTR',
            self::VEHICLE_SENT => 'VTR Enviada',
            self::AWAITING_VACANCY => 'Ag. Vaga',
            self::AWAITING_CONDUCT => 'Ag. Conduta',
            self::CONDUCT => 'Conduta',
            self::COMPLETED => 'Concluído',
            self::CANCELED => 'Cancelado',
            self::IN_ATTENDANCE_MEDICAL_REGULATION => 'Em Atendimento (Regulação Médica)',
            self::IN_ATTENDANCE_SCENE_RECORD => 'Em Atendimento (Registro de Cena)',
            self::IN_ATTENDANCE_RADIO_OPERATION => 'Em Atendimento (Rádio Operação)',
            self::AWAITING_RETURN => 'Ag. Retorno',
            self::LINKED => 'Vinculado',
            self::NO_VEHICLE_RESPONSE => 'Sem Resposta da VTR',
        };
    }

    public function messageIdentifier(): string
    {
        return match ($this) {
            self::VEHICLE_SENT => 'vehicle_sent',
            self::COMPLETED => 'completed',
            self::CANCELED => 'canceled',
        };
    }

    public const ALL_STATUSES_IN_ATTENDANCE = [
        self::IN_ATTENDANCE_MEDICAL_REGULATION,
        self::IN_ATTENDANCE_SCENE_RECORD,
        self::IN_ATTENDANCE_RADIO_OPERATION,
    ];

    public const INDEX_STATUSES = [
        self::AWAITING_MEDICAL_REGULATION,
        self::AWAITING_VEHICLE_COMMITMENT,
        self::VEHICLE_SENT,
        self::AWAITING_VACANCY,
        self::AWAITING_CONDUCT,
        self::CONDUCT,
        self::AWAITING_RETURN,
        self::AWAITING_VACANCY,
        self::NO_VEHICLE_RESPONSE,
        ...self::ALL_STATUSES_IN_ATTENDANCE,
    ];

    public const FINISHED_STATUSES = [
        self::COMPLETED,
        self::CANCELED,
        self::LINKED,
    ];

    public const ALL_WHATSAPP_NOTIFIABLE_STATUSES = [
        self::VEHICLE_SENT,
        self::COMPLETED,
        self::CANCELED,
    ];
}
