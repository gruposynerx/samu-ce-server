<?php

namespace App\Http\Controllers;

use App\Http\Resources\AttendanceRecordHistoryCollection;
use App\Models\AttendanceEvolution;
use App\Models\AttendanceObservation;
use App\Models\MedicalRegulation;
use App\Models\RadioOperationNote;
use Illuminate\Support\Facades\DB;
use Knuckles\Scribe\Attributes\Group;

#[Group(name: 'Histórico de registros', description: 'Seção responsável pelo histórico de registros do atendimento')]
class AttendanceRecordHistoryController
{
    /**
     * GET api/attendance-record-history/{attendanceId}
     *
     * Busca o histórico de registros do atendimento.
     *
     * @urlParam attendanceId string required ID do atendimento.
     */
    public function show(string $attendanceId): AttendanceRecordHistoryCollection
    {
        $medicalRegulations = MedicalRegulation::join('attendances', 'attendances.id', '=', 'medical_regulations.attendance_id')
            ->with('diagnosticHypotheses')
            ->leftJoin('users', 'users.id', '=', 'medical_regulations.created_by')
            ->leftJoin('occupations', 'occupations.code', '=', 'users.cbo')
            ->leftJoin('vehicle_movement_codes', 'vehicle_movement_codes.id', '=', 'medical_regulations.vehicle_movement_code_id')
            ->leftJoin('nature_types', 'nature_types.id', '=', 'medical_regulations.nature_type_id')
            ->leftJoin('priority_types', 'priority_types.id', '=', 'medical_regulations.priority_type_id')
            ->where('attendance_id', $attendanceId)
            ->select([
                'medical_regulations.id',
                DB::raw('1 as "type"'),
                'users.name as responsible_professional',
                'users.council_number as responsible_professional_council_number',
                'medical_regulations.medical_regulation as note',
                'medical_regulations.created_at as datetime',
                'vehicle_movement_codes.id as vehicle_movement_code_id',
                'nature_types.id as nature_type_id',
                'priority_types.id as priority_type_id',
                'medical_regulations.action_type_id as action_type_id',
                DB::raw('medical_regulations.action_details::jsonb as action_details'),
                Db::raw('medical_regulations.supporting_organizations::jsonb as supporting_organizations'),
                DB::raw('false as sent_by_app'),
                'occupations.name as responsible_professional_occupation',
            ]);

        $attendanceObservations = AttendanceObservation::join('attendances', 'attendances.id', '=', 'attendance_observations.attendance_id')
            ->leftJoin('users', 'users.id', '=', 'attendance_observations.created_by')
            ->leftJoin('occupations', 'occupations.code', '=', 'users.cbo')
            ->where('attendance_id', $attendanceId)
            ->select([
                'attendance_observations.id',
                DB::raw('2 as "type"'),
                'users.name as responsible_professional',
                'users.council_number as responsible_professional_council_number',
                'attendance_observations.observation as note',
                'attendance_observations.created_at as datetime',
                DB::raw('null as "vehicle_movement_code_id"'),
                DB::raw('null as "nature_type_id"'),
                DB::raw('null as "priority_type_id"'),
                DB::raw('(null) as "action_type_id"'),
                DB::raw('(null)::jsonb as "action_details"'),
                DB::raw('(null)::jsonb as "supporting_organizations"'),
                'attendance_observations.sent_by_app',
                'occupations.name as responsible_professional_occupation',
            ]);

        $radioOperationObservations = RadioOperationNote::join('radio_operations', 'radio_operations.id', '=', 'radio_operation_notes.radio_operation_id')
            ->leftJoin('users', 'users.name', '=', 'radio_operation_notes.responsible_professional')
            ->leftJoin('occupations', 'occupations.code', '=', 'users.cbo')
            ->where('radio_operations.attendance_id', $attendanceId)
            ->select([
                'radio_operation_notes.id',
                DB::raw('3 as "type"'),
                'radio_operation_notes.responsible_professional as responsible_professional',
                DB::raw('null as "responsible_professional_council_number"'),
                'radio_operation_notes.observation as note',
                'radio_operation_notes.datetime',
                DB::raw('null as "vehicle_movement_code_id"'),
                DB::raw('null as "nature_type_id"'),
                DB::raw('null as "priority_type_id"'),
                DB::raw('(null) as "action_type_id"'),
                DB::raw('(null)::jsonb as "action_details"'),
                DB::raw('(null)::jsonb as "supporting_organizations"'),
                DB::raw('false as sent_by_app'),
                'occupations.name as responsible_professional_occupation',
            ]);

        $attendanceEvolutions = AttendanceEvolution::join('attendances', 'attendances.id', '=', 'attendance_evolutions.attendance_id')
            ->leftJoin('users', 'users.id', '=', 'attendance_evolutions.created_by')
            ->leftJoin('occupations', 'occupations.code', '=', 'users.cbo')
            ->where('attendance_id', $attendanceId)
            ->select([
                'attendance_evolutions.id',
                DB::raw('4 as "type"'),
                'users.name as medical_regulator',
                'users.council_number as responsible_professional_council_number',
                'attendance_evolutions.evolution as evolution',
                'attendance_evolutions.created_at as datetime',
                DB::raw('null as "vehicle_movement_code_id"'),
                DB::raw('null as "nature_type_id"'),
                DB::raw('null as "priority_type_id"'),
                DB::raw('(null) as "action_type_id"'),
                DB::raw('(null)::jsonb as "action_details"'),
                DB::raw('(null)::jsonb as "supporting_organizations"'),
                DB::raw('false as sent_by_app'),
                'occupations.name as responsible_professional_occupation',
            ]);

        $data = $medicalRegulations->union($attendanceObservations)
            ->union($radioOperationObservations)
            ->union($attendanceEvolutions)
            ->orderByDesc('datetime')
            ->paginate(10);

        return new AttendanceRecordHistoryCollection($data);
    }
}
