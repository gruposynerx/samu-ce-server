<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexAttendanceConsultationRequest;
use App\Http\Resources\AttendanceConsultationCollection;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;
use App\Http\Resources\AttendanceConsultationNatureResource;
use App\Http\Resources\AttendanceConsultationHdResource;
use App\Http\Requests\AttendanceNatureConsultationRequest;

#[Group(name: 'Relatórios', description: 'Seção responsável pela gestão de relatórios')]
#[Subgroup(name: 'Consulta de Ocorrências')]
class AttendanceConsultationController extends Controller
{
    /**
     * GET api/attendance/consultation
     *
     * Retorna uma lista páginada de atendimentos (filtrados ou não).
     */
    public function index(IndexAttendanceConsultationRequest $request): ResourceCollection|JsonResponse
    {
        $data = $request->validated();

        $baseQuery = DB::table('tickets')->where('tickets.urc_id', auth()->user()->urc_id)
            ->when(isset($data['start_date'], $data['end_date']), function ($query) use ($data) {
                $query->where('tickets.opening_at', '>=', Carbon::create($data['start_date']))
                    ->where('tickets.opening_at', '<', Carbon::create($data['end_date'])->addDay());
            })
            ->when(isset($data['cities']), function ($query) use ($data) {
                $query->whereIn('tickets.city_id', $data['cities']);
            })
            ->when(isset($data['ticket_types']), function ($query) use ($data) {
                $query->whereIn('tickets.ticket_type_id', $data['ticket_types']);
            })
            ->join('attendances', 'attendances.ticket_id', '=', 'tickets.id')
            ->when(isset($data['attendance_number']), function ($query) use ($data) {
                $sequences = explode('/', $data['attendance_number']);
                $ticketSequence = $sequences[0];
                $attendanceSequence = $sequences[1] ?? null;

                $query->where('tickets.ticket_sequence_per_urgency_regulation_center', $ticketSequence)->when(isset($attendanceSequence), function ($query) use ($attendanceSequence) {
                    $query->where('attendances.attendance_sequence_per_ticket', $attendanceSequence);
                });
            })
            ->when(isset($data['attendance_status_id']), function ($query) use ($data) {
                $query->where('attendances.attendance_status_id', $data['attendance_status_id']);
            })
            ->leftJoin('patients', 'patients.id', '=', 'attendances.patient_id')
            ->when(isset($data['patient_name']), function ($query) use ($data) {
                $query->whereRaw('unaccent(patients.name) ilike unaccent(?)', "%{$data['patient_name']}%");
            })
            ->when(isset($data['initial_birth_date'], $data['final_birth_date'], $data['time_unit_id']), function ($query) use ($data) {
                $query->whereBetween('patients.age', [$data['initial_birth_date'], $data['final_birth_date']])
                    ->where('patients.time_unit_id', $data['time_unit_id']);
            })
            ->when(isset($data['gender_code']), function ($query) use ($data) {
                $query->where('patients.gender_code', $data['gender_code']);
            })
            ->leftJoin('requesters', 'requesters.id', '=', 'tickets.requester_id')
            ->when(isset($data['requesting_name']), function ($query) use ($data) {
                $query->whereRaw('unaccent(requesters.name) ilike unaccent(?)', "%{$data['requesting_name']}%");
            })
            ->when(isset($data['requesting_phone']), function ($query) use ($data) {
                $query->where(function ($query) use ($data) {
                    $sanitizedInput = '%' . str_replace(' ', '%', $data['requesting_phone']) . '%';

                    $query->whereRaw('requesters.primary_phone ilike unaccent(?)', $sanitizedInput)
                        ->orWhereRaw('requesters.secondary_phone ilike unaccent(?)', $sanitizedInput);
                });
            })
            ->when(isset($data['requester_types']), function ($query) use ($data) {
                $query->whereIn('requesters.requester_type_id', $data['requester_types']);
            })
            ->when(isset($data['search']), function ($query) use ($data) {
                $sequences = processSequences($data['search']);
                $query->where(function ($query) use ($data, $sequences) {
                    $query->whereRaw('unaccent(requesters.name) ilike unaccent(?)', "%{$data['search']}%")
                        ->orWhereRaw('unaccent(patients.name) ilike unaccent(?)', "%{$data['search']}%")
                        ->orWhere('tickets.ticket_sequence_per_urgency_regulation_center', $sequences['ticketSequence'])
                        ->when(!empty($sequences['attendanceSequence']), function ($query) use ($sequences) {
                            $query->where('attendances.attendance_sequence_per_ticket', $sequences['attendanceSequence']);
                        });
                });
            })
            ->leftJoinSub(
                DB::table('medical_regulations')
                    ->select('id', 'attendance_id', 'created_by', 'priority_type_id', 'created_at')
                    ->addSelect(DB::raw('ROW_NUMBER() OVER (PARTITION BY attendance_id ORDER BY created_at DESC) as row_num')),
                'latest_medical_regulation',
                function ($join) {
                    $join->on('attendances.id', '=', 'latest_medical_regulation.attendance_id')
                        ->where('latest_medical_regulation.row_num', 1);
                }
            )
            ->when(isset($data['supporting_organizations_medical_regulation']), function ($query) use ($data) {
                $query->where(function ($query) use ($data) {
                    foreach ($data['supporting_organizations_medical_regulation'] as $supportingOrganization) {
                        $query->orWhereJsonContains('latest_medical_regulation.supporting_organizations', $supportingOrganization);
                    }
                });
            })
            ->when(isset($data['action_types']), function ($query) use ($data) {
                $query->whereIn('latest_medical_regulation.action_type_id', $data['action_types']);
            })
            ->when(isset($data['priority_types']), function ($query) use ($data) {
                $query->whereIn('latest_medical_regulation.priority_type_id', $data['priority_types']);
            })
            ->when(isset($data['vehicle_movement_codes']), function ($query) use ($data) {
                $query->whereIn('latest_medical_regulation.vehicle_movement_code_id', $data['vehicle_movement_codes']);
            })
            ->leftJoinSub(
                DB::table('scene_recordings')
                    ->select('id', 'created_at', 'created_by', 'attendance_id', 'priority_type_id', 'unit_destination_id', 'support_needed_description', 'closing_type_id')
                    ->addSelect(DB::raw('ROW_NUMBER() OVER (PARTITION BY attendance_id ORDER BY created_at DESC) as row_num')),
                'latest_scene_recording',
                function ($join) {
                    $join->on('attendances.id', '=', 'latest_scene_recording.attendance_id')
                        ->where('latest_scene_recording.row_num', 1);
                }
            )
            ->when(isset($data['closing_types']), function ($query) use ($data) {
                $query->whereIn('latest_scene_recording.closing_type_id', $data['closing_types']);
            })
            ->when(isset($data['antecedents']), function ($query) use ($data) {
                $query->join('scene_recording_antecedents', 'scene_recording_antecedents.scene_recording_id', '=', 'latest_scene_recording.id')
                    ->whereIn('scene_recording_antecedents.antecedent_type_id', $data['antecedents']);
            })
            ->when(isset($data['supporting_organizations_scene_recording']), function ($query) use ($data) {
                $query->where(function ($query) use ($data) {
                    foreach ($data['supporting_organizations_scene_recording'] as $supportingOrganization) {
                        $query->orWhereJsonContains('latest_scene_recording.support_needed_description', $supportingOrganization);
                    }
                });
            })
            ->leftJoinSub(
                DB::table('form_diagnostic_hypotheses')
                    ->select('id', 'attendance_id', 'diagnostic_hypothesis_id', 'created_at', 'recommended', 'applied', 'nature_type_id')
                    ->addSelect(DB::raw('ROW_NUMBER() OVER (PARTITION BY attendance_id ORDER BY created_at DESC) as row_num')),
                'latest_diagnostic_hypothesis',
                function ($join) {
                    $join->on('attendances.id', '=', 'latest_diagnostic_hypothesis.attendance_id')
                        ->where('latest_diagnostic_hypothesis.row_num', 1);
                }
            )
            ->when(isset($data['diagnostic_hypotheses']), function ($query) use ($data) {
                $query->whereIn('latest_diagnostic_hypothesis.diagnostic_hypothesis_id', $data['diagnostic_hypotheses']);
            })
            ->when(isset($data['thrombolytic_recommended']), function ($query) use ($data) {
                $query->whereIn('latest_diagnostic_hypothesis.recommended', $data['thrombolytic_recommended']);
            })
            ->when(isset($data['thrombolytic_applied']), function ($query) use ($data) {
                $query->whereIn('latest_diagnostic_hypothesis.applied', $data['thrombolytic_applied']);
            })
            ->when(isset($data['nature_types']), function ($query) use ($data) {
                $query->whereIn('latest_diagnostic_hypothesis.nature_type_id', $data['nature_types']);
            })
            ->leftJoinSub(
                DB::table('vehicle_status_histories')
                    ->select('id', 'attendance_id', 'created_at', 'vehicle_type_id', 'vehicle_id', 'base_id')
                    ->addSelect(DB::raw('ROW_NUMBER() OVER (PARTITION BY attendance_id ORDER BY created_at DESC) as row_num')),
                'latest_status',
                function ($join) {
                    $join->on('attendances.id', '=', 'latest_status.attendance_id')
                        ->where('latest_status.row_num', 1);
                }
            )
            ->when(isset($data['vehicles_types']), function (Builder $query) use ($data) {
                $query->whereIn('latest_status.vehicle_type_id', $data['vehicles_types']);
            })
            ->when(isset($data['bases']), function (Builder $query) use ($data) {
                $query->whereIn('latest_status.base_id', $data['bases']);
            })
            ->leftJoin('vehicles', 'vehicles.id', '=', 'latest_status.vehicle_id')
            ->when(isset($data['vehicles']), function ($query) use ($data) {
                $query->whereIn('vehicles.id', $data['vehicles']);
            })
            ->leftJoinSub(
                DB::table('user_attendances')
                    ->select('id', 'attendance_id', 'user_id', 'created_at')
                    ->addSelect(DB::raw('ROW_NUMBER() OVER (PARTITION BY attendance_id ORDER BY created_at DESC) as row_num')),
                'latest_user_attendance',
                function ($join) {
                    $join->on('attendances.id', '=', 'latest_user_attendance.attendance_id')
                        ->where('latest_user_attendance.row_num', 1);
                }
            )
            ->leftJoinSub(
                DB::table('radio_operations')
                    ->select('id', 'attendance_id', 'created_at', 'created_by')
                    ->addSelect(DB::raw('ROW_NUMBER() OVER (PARTITION BY attendance_id ORDER BY created_at DESC) as row_num')),
                'latest_radio_operation',
                function ($join) {
                    $join->on('attendances.id', '=', 'latest_radio_operation.attendance_id')
                        ->where('latest_radio_operation.row_num', 1);
                }
            )
            ->when(isset($data['user_id']), function ($query) use ($data) {
                $query->where(function ($query) use ($data) {
                    $query->where('tickets.created_by', $data['user_id'])
                        ->orWhere('latest_medical_regulation.created_by', $data['user_id'])
                        ->orWhere('latest_scene_recording.created_by', $data['user_id'])
                        ->orWhere('latest_radio_operation.created_by', $data['user_id']);
                });
            })
            ->leftJoinSub(
                DB::table('scene_recording_destination_unit_histories')
                    ->select('id', 'scene_recording_id', 'created_at', 'created_by', 'unit_destination_id')
                    ->addSelect(DB::raw('ROW_NUMBER() OVER (PARTITION BY scene_recording_id ORDER BY created_at DESC) as row_num')),
                'latest_destination_unit_history',
                function ($join) {
                    $join->on('latest_scene_recording.id', '=', 'latest_destination_unit_history.scene_recording_id')
                        ->where('latest_destination_unit_history.row_num', 1);
                }
            )
            ->leftJoin('users as scene_recording_creator', 'scene_recording_creator.id', '=', 'latest_scene_recording.created_by')
            ->leftJoin('units as scene_recording_destination_unit_histories_destination', 'scene_recording_destination_unit_histories_destination.id', '=', 'latest_destination_unit_history.unit_destination_id')
            ->leftJoin('cities as tickets_city', 'tickets_city.id', '=', 'tickets.city_id')
            ->leftJoin('users as medical_regulators', 'medical_regulators.id', '=', 'latest_medical_regulation.created_by')
            ->leftJoin('diagnostic_hypotheses', 'diagnostic_hypotheses.id', '=', 'latest_diagnostic_hypothesis.diagnostic_hypothesis_id')
            ->leftJoin('users as precursors', 'precursors.id', '=', 'latest_user_attendance.user_id')
            ->leftJoin('attendance_links', 'attendance_links.children_link_id', '=', 'attendances.id')
            ->leftJoin('attendances as father_link', 'father_link.id', '=', 'attendance_links.father_link_id')
            ->leftJoin('tickets as father_link_ticket', 'father_link_ticket.id', '=', 'father_link.ticket_id');

        $primaryAttendancesQuery = (clone $baseQuery)->where('attendances.attendable_type', 'primary_attendance')
            ->join('primary_attendances', 'attendances.attendable_id', '=', 'primary_attendances.id')
            ->when(isset($data['neighborhood']), function ($query) use ($data) {
                $query->whereRaw('unaccent(primary_attendances.neighborhood) ilike unaccent(?)', "%{$data['neighborhood']}%");
            })
            ->when(isset($data['street']), function ($query) use ($data) {
                $query->whereRaw('unaccent(primary_attendances.street) ilike unaccent(?)', "%{$data['street']}%");
            })
            ->when(isset($data['distance_types']), function ($query) use ($data) {
                $query->whereIn('primary_attendances.distance_type_id', $data['distance_types']);
            })
            ->when(isset($data['location_types']), function ($query) use ($data) {
                $query->whereIn('primary_attendances.location_type_id', $data['location_types']);
            })
            ->when(isset($data['units_destination']), function ($query) use ($data) {
                $query->where(function ($query) use ($data) {
                    $query->whereIn('latest_scene_recording.unit_destination_id', $data['units_destination']);
                });
            })
            ->selectRaw(
                "attendances.attendable_type as attendable_type,
                attendances.id as attendance_id, 
                attendances.attendance_status_id, 
                attendances.created_at, 
                attendances.updated_at, 
                attendances.attendance_sequence_per_ticket, 
                tickets.ticket_sequence_per_urgency_regulation_center, 
                tickets.opening_at,
                tickets.ticket_type_id,
                tickets.city_id,
                tickets_city.name as tickets_city_name,
                diagnostic_hypotheses.name as diagnostic_hypothesis_name,
                primary_attendances.neighborhood,
                '' as primary_complaint,
                '' as secondary_attendance_destination_name,
                latest_medical_regulation.priority_type_id,
                latest_medical_regulation.created_at as medical_regulation_created_at,
                latest_medical_regulation.id as medical_regulation_id,
                medical_regulators.name as medical_regulator_name,
                latest_scene_recording.created_at as scene_recording_created_at,
                latest_scene_recording.priority_type_id as scene_recording_priority_type_id,
                latest_scene_recording.id as scene_recording_id, 
                scene_recording_destination_unit_histories_destination.name as scene_recording_destination_unit_histories_destination_name, 
                scene_recording_creator.name as scene_recording_creator_name, 
                patients.name as patient_name, 
                patients.age as patient_age, 
                patients.time_unit_id as patient_time_unit_id, 
                vehicles.*, 
                precursors.name as precursor_name, 
                requesters.name as requesting_name, 
                latest_diagnostic_hypothesis.diagnostic_hypothesis_id, 
                latest_status.vehicle_type_id, 
                father_link.id as father_link_id, 
                father_link.attendance_sequence_per_ticket as father_link_attendance_sequence_per_ticket, 
                father_link_ticket.ticket_sequence_per_urgency_regulation_center as father_link_ticket_sequence_per_urgency_regulation_center"
            );

        $secondaryAttendancesQuery = (clone $baseQuery)->where('attendances.attendable_type', 'secondary_attendance')
            ->join('secondary_attendances', 'attendances.attendable_id', '=', 'secondary_attendances.id')
            ->when(isset($data['transfer_reason_id']), function ($query) use ($data) {
                $query->where('secondary_attendances.transfer_reason_id', $data['transfer_reason_id']);
            })
            ->when(isset($data['units_origin']), function ($query) use ($data) {
                $query->whereIn('secondary_attendances.unit_origin_id', $data['units_origin']);
            })
            ->when(isset($data['units_destination']), function ($query) use ($data) {
                $query->where(function ($query) use ($data) {
                    $query->whereIn('secondary_attendances.unit_destination_id', $data['units_destination'])
                        ->orWhereIn('latest_scene_recording.unit_destination_id', $data['units_destination']);
                });
            })
            ->leftJoin('units as secondary_attendance_destination', 'secondary_attendance_destination.id', '=', 'secondary_attendances.unit_destination_id')
            ->selectRaw(
                "attendances.attendable_type as attendable_type,
                attendances.id as attendance_id, 
                attendances.attendance_status_id, 
                attendances.created_at, 
                attendances.updated_at, 
                attendances.attendance_sequence_per_ticket, 
                tickets.ticket_sequence_per_urgency_regulation_center, 
                tickets.opening_at, 
                tickets.ticket_type_id, 
                tickets.city_id, 
                tickets_city.name as tickets_city_name,
                diagnostic_hypotheses.name as diagnostic_hypothesis_name,
                '' as neighborhood, 
                '' as primary_complaint, 
                secondary_attendance_destination.name as secondary_attendance_destination_name, 
                latest_medical_regulation.priority_type_id, 
                latest_medical_regulation.created_at as medical_regulation_created_at, 
                latest_medical_regulation.id as medical_regulation_id, 
                medical_regulators.name as medical_regulator_name, 
                latest_scene_recording.created_at as scene_recording_created_at, 
                latest_scene_recording.priority_type_id as scene_recording_priority_type_id, 
                latest_scene_recording.id as scene_recording_id, 
                scene_recording_destination_unit_histories_destination.name as scene_recording_destination_unit_histories_destination_name, 
                scene_recording_creator.name as scene_recording_creator_name, 
                patients.name as patient_name, 
                patients.age as patient_age, 
                patients.time_unit_id as patient_time_unit_id, 
                vehicles.*, 
                precursors.name as precursor_name, 
                requesters.name as requesting_name, 
                latest_diagnostic_hypothesis.diagnostic_hypothesis_id,
                latest_status.vehicle_type_id, 
                father_link.id as father_link_id, 
                father_link.attendance_sequence_per_ticket as father_link_attendance_sequence_per_ticket, 
                father_link_ticket.ticket_sequence_per_urgency_regulation_center as father_link_ticket_sequence_per_urgency_regulation_center"
            );

        $otherAttendancesQuery = (clone $baseQuery)->where('attendances.attendable_type', 'other_attendance')
            ->join('other_attendances', 'attendances.attendable_id', '=', 'other_attendances.id')
            ->when(isset($data['units_destination']), function ($query) use ($data) {
                $query->where(function ($query) use ($data) {
                    $query->whereIn('latest_scene_recording.unit_destination_id', $data['units_destination']);
                });
            })
            ->selectRaw(
                "attendances.attendable_type as attendable_type,
                attendances.id as attendance_id, 
                attendances.attendance_status_id, 
                attendances.created_at, 
                attendances.updated_at, 
                attendances.attendance_sequence_per_ticket, 
                tickets.ticket_sequence_per_urgency_regulation_center, 
                tickets.opening_at, 
                tickets.ticket_type_id, 
                tickets.city_id, 
                tickets_city.name as tickets_city_name,
                diagnostic_hypotheses.name as diagnostic_hypothesis_name, 
                '' as neighborhood, 
                '' as primary_complaint, 
                '' as secondary_attendance_destination_name,
                latest_medical_regulation.priority_type_id,
                latest_medical_regulation.created_at as medical_regulation_created_at,
                latest_medical_regulation.id as medical_regulation_id,
                medical_regulators.name as medical_regulator_name, 
                latest_scene_recording.created_at as scene_recording_created_at, 
                latest_scene_recording.priority_type_id as scene_recording_priority_type_id, 
                latest_scene_recording.id as scene_recording_id, 
                scene_recording_destination_unit_histories_destination.name as scene_recording_destination_unit_histories_destination_name, 
                scene_recording_creator.name as scene_recording_creator_name, 
                patients.name as patient_name, 
                patients.age as patient_age, 
                patients.time_unit_id as patient_time_unit_id, 
                vehicles.*, 
                precursors.name as precursor_name, 
                requesters.name as requesting_name, 
                latest_diagnostic_hypothesis.diagnostic_hypothesis_id, 
                latest_status.vehicle_type_id, 
                father_link.id as father_link_id, 
                father_link.attendance_sequence_per_ticket as father_link_attendance_sequence_per_ticket, 
                father_link_ticket.ticket_sequence_per_urgency_regulation_center as father_link_ticket_sequence_per_urgency_regulation_center"
            );

        $query = $primaryAttendancesQuery->union($secondaryAttendancesQuery)->union($otherAttendancesQuery)
            ->distinct('attendance_id')
            ->orderByDesc('opening_at');
        $results = $request->get('list_all')
            ? $query->get()
            : $query->paginate($request->validated('per_page', 15));
        $results->each(function ($result) {
            $result->diagnostic_hypotheses = array_merge($result->diagnostic_hypotheses ?? [], [$result->diagnostic_hypothesis_name]);
        });

        
        return new AttendanceConsultationCollection($results);
    }

    public function attendanceConsultationNature(AttendanceNatureConsultationRequest $request): ResourceCollection|JsonResponse
    {
        $data = $request->validated();
        $query = DB::table('tickets as t')
            ->join('attendances as a', 'a.ticket_id', '=', 't.id')
            ->join('patients as p', 'p.id', '=', 'a.patient_id')
            ->when(isset($data['start_date'], $data['end_date']), function ($query) use ($data) {
                $query->whereRaw("(t.opening_at at time zone 'utc' at time zone 'America/Fortaleza') >= ?", [
                    Carbon::create($data['start_date'])->startOfDay()->toDateTimeString()
                ])
                    ->whereRaw("(t.opening_at at time zone 'utc' at time zone 'America/Fortaleza') < ?", [
                        Carbon::create($data['end_date'])->addDay()->startOfDay()->toDateTimeString()
                    ]);
            })
            ->join(
                DB::raw('(SELECT DISTINCT ON (vsh2.attendance_id) * 
                      FROM vehicle_status_histories vsh2 
                      WHERE vsh2.base_id IS NOT NULL 
                      ORDER BY vsh2.attendance_id, vsh2.created_at DESC) AS vsh'),
                'vsh.attendance_id',
                '=',
                'a.id'
            )
            ->join('bases as b', 'b.id', '=', 'vsh.base_id')
            ->join('urgency_regulation_centers as urc', 'urc.id', '=', 'a.urc_id')
            ->leftJoin('regional_groups as rg', 'rg.id', '=', 'b.regional_group_id')
            ->join(DB::raw('
        (SELECT DISTINCT ON (ro2.attendance_id) * FROM radio_operations ro2 ORDER BY ro2.attendance_id, ro2.created_at DESC) AS ro
        '), 'ro.attendance_id', '=', 'a.id')
            ->leftJoin('secondary_attendances as sa', function ($join) {
                $join->on('sa.id', '=', 'a.attendable_id')
                    ->where('a.attendable_type', '=', 'secondary_attendance');
            })
            ->leftJoin('primary_attendances as pa', function ($join) {
                $join->on('pa.id', '=', 'a.attendable_id')
                    ->where('a.attendable_type', '=', 'primary_attendance');
            })
            ->join(
                DB::raw('(SELECT DISTINCT ON (fdh2.attendance_id) * 
          FROM form_diagnostic_hypotheses fdh2 
          ORDER BY fdh2.attendance_id, fdh2.created_at DESC) AS fdh'),
                'fdh.attendance_id',
                '=',
                'a.id'
            )->join('diagnostic_hypotheses as dh', 'dh.id', '=', 'fdh.diagnostic_hypothesis_id')
            ->join('nature_types as nt', 'nt.id', '=', 'fdh.nature_type_id')
            ->where('t.urc_id', '=', auth()->user()->urc_id);

        $query->when(!empty($data['time_unit_id']), function ($q) use ($data) {
            if (!empty($data['initial_birth_date']) && !empty($data['final_birth_date'])) {
                $q->whereBetween('p.age', [$data['initial_birth_date'], $data['final_birth_date']]);
            } elseif (!empty($data['initial_birth_date'])) {
                $q->where('p.age', '>=', $data['initial_birth_date']);
            } elseif (!empty($data['final_birth_date'])) {
                $q->where('p.age', '<=', $data['final_birth_date']);
            }
            $q->where('p.time_unit_id', $data['time_unit_id']);
        });
        $query->whereNotNull('ro.arrived_to_site_at')
            ->whereNotNull('t.opening_at')
            ->whereColumn('t.opening_at', '<=', 'ro.arrived_to_site_at');
        $query->when(!empty($data['nature_types']), fn($q) => $q->whereIn('nt.id', $data['nature_types']));
        $query->when(!empty($data['cities']), fn($q) => $q->whereIn('b.city_id', $data['cities']));
        $query->when(!empty($data['bases']), fn($q) => $q->whereIn('b.id', $data['bases']));
        $query->when(!empty($data['diagnostic_hypotheses']), fn($q) => $q->whereIn('fdh.diagnostic_hypothesis_id', $data['diagnostic_hypotheses']));
        $query->when(!empty($data['groups_regional']), fn($q) => $q->whereIn('b.regional_group_id', $data['groups_regional']));
        $query->when(!empty($data['units_destination']), function ($query) use ($data) {
            $query->where(function ($q) use ($data) {
                $q->where(function ($q1) use ($data) {
                    $q1->where('a.attendable_type', 'secondary_attendance')
                        ->whereIn('sa.unit_destination_id', $data['units_destination']);
                })->orWhere(function ($q2) use ($data) {
                    $q2->where('a.attendable_type', 'primary_attendance')
                        ->whereIn('pa.unit_destination_id', $data['units_destination']);
                });
            });
        });
        $query->select([
            'nt.name as nature',
            DB::raw("COUNT(DISTINCT CASE WHEN a.attendable_type = 'primary_attendance' THEN a.id END) AS primary_attendances"),
            DB::raw("COUNT(DISTINCT CASE WHEN a.attendable_type = 'secondary_attendance' THEN a.id END) AS secondary_attendances"),
            DB::raw("COUNT(DISTINCT a.id) AS total_attendances"),

            DB::raw("AVG(CASE 
                WHEN a.attendable_type = 'primary_attendance' 
                    AND ro.arrived_to_site_at IS NOT NULL 
                    AND t.opening_at IS NOT NULL 
                THEN EXTRACT(EPOCH FROM (ro.arrived_to_site_at - t.opening_at)) / 3600 
                ELSE NULL 
            END) AS primary_attendances_average"),

            DB::raw("AVG(CASE 
                    WHEN a.attendable_type = 'secondary_attendance' 
                        AND ro.arrived_to_site_at IS NOT NULL 
                        AND t.opening_at IS NOT NULL 
                    THEN EXTRACT(EPOCH FROM (ro.arrived_to_site_at - t.opening_at)) / 3600 
                    ELSE NULL 
            END) AS secondary_attendances_average"),

            DB::raw("(
                SUM(CASE 
                    WHEN a.attendable_type = 'primary_attendance' 
                        AND ro.arrived_to_site_at IS NOT NULL 
                        AND t.opening_at IS NOT NULL 
                    THEN EXTRACT(EPOCH FROM (ro.arrived_to_site_at - t.opening_at)) 

                    WHEN a.attendable_type = 'secondary_attendance' 
                        AND ro.arrived_to_site_at IS NOT NULL 
                        AND t.opening_at IS NOT NULL
                    THEN EXTRACT(EPOCH FROM (ro.arrived_to_site_at - t.opening_at)) 

                    ELSE 0 
                END) 
                /
                NULLIF(
                    COUNT(CASE 
                        WHEN (
                            (a.attendable_type = 'primary_attendance' AND ro.arrived_to_site_at IS NOT NULL AND t.opening_at IS NOT NULL) OR
                            (a.attendable_type = 'secondary_attendance' AND ro.arrived_to_site_at IS NOT NULL AND t.opening_at IS NOT NULL)
                        )
                        THEN 1 
                    END), 0
                )
            ) / 3600 AS total_attendances_average")
        ])
            ->groupBy('nt.name')
            ->orderByDesc('total_attendances');
        $results = $request->get('list_all')
            ? $query->get()
            : $query->paginate($request->validated('per_page', 15));

        $results->each(function ($result) {
            $result->diagnostic_hypotheses = array_merge($result->diagnostic_hypotheses ?? [], [$result->diagnostic_hypothesis_name]);
        });
        
        return new AttendanceConsultationCollection($results);
    }

    public function attendanceConsultationNature(AttendanceNatureConsultationRequest $request): ResourceCollection|JsonResponse
    {
        $data = $request->validated();
        $query = DB::table('tickets as t')
            ->join('attendances as a', 'a.ticket_id', '=', 't.id')
            ->join('patients as p', 'p.id', '=', 'a.patient_id')
            ->when(isset($data['start_date'], $data['end_date']), function ($query) use ($data) {
                $query->whereRaw("(t.opening_at at time zone 'utc' at time zone 'America/Fortaleza') >= ?", [
                    Carbon::create($data['start_date'])->startOfDay()->toDateTimeString()
                ])
                    ->whereRaw("(t.opening_at at time zone 'utc' at time zone 'America/Fortaleza') < ?", [
                        Carbon::create($data['end_date'])->addDay()->startOfDay()->toDateTimeString()
                    ]);
            })
            ->join(
                DB::raw('(SELECT DISTINCT ON (vsh2.attendance_id) * 
                      FROM vehicle_status_histories vsh2 
                      WHERE vsh2.base_id IS NOT NULL 
                      ORDER BY vsh2.attendance_id, vsh2.created_at DESC) AS vsh'),
                'vsh.attendance_id',
                '=',
                'a.id'
            )
            ->join('bases as b', 'b.id', '=', 'vsh.base_id')
            ->join('urgency_regulation_centers as urc', 'urc.id', '=', 'a.urc_id')
            ->leftJoin('regional_groups as rg', 'rg.id', '=', 'b.regional_group_id')
            ->join(DB::raw('
        (SELECT DISTINCT ON (ro2.attendance_id) * FROM radio_operations ro2 ORDER BY ro2.attendance_id, ro2.created_at DESC) AS ro
        '), 'ro.attendance_id', '=', 'a.id')
            ->leftJoin('secondary_attendances as sa', function ($join) {
                $join->on('sa.id', '=', 'a.attendable_id')
                    ->where('a.attendable_type', '=', 'secondary_attendance');
            })
            ->leftJoin('primary_attendances as pa', function ($join) {
                $join->on('pa.id', '=', 'a.attendable_id')
                    ->where('a.attendable_type', '=', 'primary_attendance');
            })
            ->join(
                DB::raw('(SELECT DISTINCT ON (fdh2.attendance_id) * 
          FROM form_diagnostic_hypotheses fdh2 
          ORDER BY fdh2.attendance_id, fdh2.created_at DESC) AS fdh'),
                'fdh.attendance_id',
                '=',
                'a.id'
            )->join('diagnostic_hypotheses as dh', 'dh.id', '=', 'fdh.diagnostic_hypothesis_id')
            ->join('nature_types as nt', 'nt.id', '=', 'fdh.nature_type_id')
            ->where('t.urc_id', '=', auth()->user()->urc_id);

        $query->when(!empty($data['time_unit_id']), function ($q) use ($data) {
            if (!empty($data['initial_birth_date']) && !empty($data['final_birth_date'])) {
                $q->whereBetween('p.age', [$data['initial_birth_date'], $data['final_birth_date']]);
            } elseif (!empty($data['initial_birth_date'])) {
                $q->where('p.age', '>=', $data['initial_birth_date']);
            } elseif (!empty($data['final_birth_date'])) {
                $q->where('p.age', '<=', $data['final_birth_date']);
            }
            $q->where('p.time_unit_id', $data['time_unit_id']);
        });
        $query->whereNotNull('ro.arrived_to_site_at')
            ->whereNotNull('t.opening_at')
            ->whereColumn('t.opening_at', '<=', 'ro.arrived_to_site_at');
        $query->when(!empty($data['nature_types']), fn($q) => $q->whereIn('nt.id', $data['nature_types']));
        $query->when(!empty($data['cities']), fn($q) => $q->whereIn('b.city_id', $data['cities']));
        $query->when(!empty($data['bases']), fn($q) => $q->whereIn('b.id', $data['bases']));
        $query->when(!empty($data['diagnostic_hypotheses']), fn($q) => $q->whereIn('fdh.diagnostic_hypothesis_id', $data['diagnostic_hypotheses']));
        $query->when(!empty($data['groups_regional']), fn($q) => $q->whereIn('b.regional_group_id', $data['groups_regional']));
        $query->when(!empty($data['units_destination']), function ($query) use ($data) {
            $query->where(function ($q) use ($data) {
                $q->where(function ($q1) use ($data) {
                    $q1->where('a.attendable_type', 'secondary_attendance')
                        ->whereIn('sa.unit_destination_id', $data['units_destination']);
                })->orWhere(function ($q2) use ($data) {
                    $q2->where('a.attendable_type', 'primary_attendance')
                        ->whereIn('pa.unit_destination_id', $data['units_destination']);
                });
            });
        });
        $query->select([
            'nt.name as nature',
            DB::raw("COUNT(DISTINCT CASE WHEN a.attendable_type = 'primary_attendance' THEN a.id END) AS primary_attendances"),
            DB::raw("COUNT(DISTINCT CASE WHEN a.attendable_type = 'secondary_attendance' THEN a.id END) AS secondary_attendances"),
            DB::raw("COUNT(DISTINCT a.id) AS total_attendances"),

            DB::raw("AVG(CASE 
                WHEN a.attendable_type = 'primary_attendance' 
                    AND ro.arrived_to_site_at IS NOT NULL 
                    AND t.opening_at IS NOT NULL 
                THEN EXTRACT(EPOCH FROM (ro.arrived_to_site_at - t.opening_at)) / 3600 
                ELSE NULL 
            END) AS primary_attendances_average"),

            DB::raw("AVG(CASE 
                    WHEN a.attendable_type = 'secondary_attendance' 
                        AND ro.arrived_to_site_at IS NOT NULL 
                        AND t.opening_at IS NOT NULL 
                    THEN EXTRACT(EPOCH FROM (ro.arrived_to_site_at - t.opening_at)) / 3600 
                    ELSE NULL 
            END) AS secondary_attendances_average"),

            DB::raw("(
                SUM(CASE 
                    WHEN a.attendable_type = 'primary_attendance' 
                        AND ro.arrived_to_site_at IS NOT NULL 
                        AND t.opening_at IS NOT NULL 
                    THEN EXTRACT(EPOCH FROM (ro.arrived_to_site_at - t.opening_at)) 

                    WHEN a.attendable_type = 'secondary_attendance' 
                        AND ro.arrived_to_site_at IS NOT NULL 
                        AND t.opening_at IS NOT NULL
                    THEN EXTRACT(EPOCH FROM (ro.arrived_to_site_at - t.opening_at)) 

                    ELSE 0 
                END) 
                /
                NULLIF(
                    COUNT(CASE 
                        WHEN (
                            (a.attendable_type = 'primary_attendance' AND ro.arrived_to_site_at IS NOT NULL AND t.opening_at IS NOT NULL) OR
                            (a.attendable_type = 'secondary_attendance' AND ro.arrived_to_site_at IS NOT NULL AND t.opening_at IS NOT NULL)
                        )
                        THEN 1 
                    END), 0
                )
            ) / 3600 AS total_attendances_average")
        ])
            ->groupBy('nt.name')
            ->orderByDesc('total_attendances');

        $results = $request->get('list_all')
            ? $query->get()
            : $query->paginate($request->validated('per_page', 15));

        return AttendanceConsultationNatureResource::collection($results);
    }

    public function attendanceConsultationHd(AttendanceNatureConsultationRequest $request): ResourceCollection|JsonResponse
    {
        $data = $request->validated();
        $query = DB::table('tickets as t')
            ->where('t.urc_id', '=', auth()->user()->urc_id)
            ->when(isset($data['start_date'], $data['end_date']), function ($query) use ($data) {
                $start = Carbon::create($data['start_date'])->startOfDay()->toDateTimeString();
                $end = Carbon::create($data['end_date'])->addDay()->startOfDay()->toDateTimeString();
                $query->whereRaw("(t.opening_at at time zone 'utc' at time zone 'America/Fortaleza') >= ?", [$start])
                    ->whereRaw("(t.opening_at at time zone 'utc' at time zone 'America/Fortaleza') < ?", [$end]);
            })
            ->join('attendances as a', 'a.ticket_id', '=', 't.id')
            ->join('patients as p', 'p.id', '=', 'a.patient_id')
            ->join(
                DB::raw('(SELECT DISTINCT ON (vsh2.attendance_id) * FROM vehicle_status_histories vsh2 WHERE vsh2.base_id IS NOT NULL ORDER BY vsh2.attendance_id, vsh2.created_at DESC) AS vsh'),
                'vsh.attendance_id',
                '=',
                'a.id'
            )
            ->join('bases as b', 'b.id', '=', 'vsh.base_id')
            ->join('urgency_regulation_centers as urc', 'urc.id', '=', 'a.urc_id')
            ->leftJoin('regional_groups as rg', 'rg.id', '=', 'b.regional_group_id')
            ->join('radio_operations as ro', 'ro.attendance_id', '=', 'a.id')
            ->leftJoin('secondary_attendances as sa', function ($join) {
                $join->on('sa.id', '=', 'a.attendable_id')
                    ->where('a.attendable_type', '=', 'secondary_attendance');
            })
            ->leftJoin('primary_attendances as pa', function ($join) {
                $join->on('pa.id', '=', 'a.attendable_id')
                    ->where('a.attendable_type', '=', 'primary_attendance');
            })
            ->join(
                DB::raw('(SELECT DISTINCT ON (fdh2.attendance_id) * FROM form_diagnostic_hypotheses fdh2 ORDER BY fdh2.attendance_id, fdh2.created_at DESC) AS fdh'),
                'fdh.attendance_id',
                '=',
                'a.id'
            )
            ->join('diagnostic_hypotheses as dh', 'dh.id', '=', 'fdh.diagnostic_hypothesis_id')
            ->join('nature_types as nt', 'nt.id', '=', 'fdh.nature_type_id');

        $query->when(!empty($data['time_unit_id']), function ($q) use ($data) {
            if (!empty($data['initial_birth_date']) && !empty($data['final_birth_date'])) {
                $q->whereBetween('p.age', [$data['initial_birth_date'], $data['final_birth_date']]);
            } elseif (!empty($data['initial_birth_date'])) {
                $q->where('p.age', '>=', $data['initial_birth_date']);
            } elseif (!empty($data['final_birth_date'])) {
                $q->where('p.age', '<=', $data['final_birth_date']);
            }
            $q->where('p.time_unit_id', $data['time_unit_id']);
        });
        $query->whereNotNull('ro.arrived_to_site_at')
            ->whereNotNull('t.opening_at')
            ->whereColumn('t.opening_at', '<=', 'ro.arrived_to_site_at');
        $query->when(!empty($data['nature_types']), fn($q) => $q->whereIn('nt.id', $data['nature_types']));
        $query->when(!empty($data['cities']), fn($q) => $q->whereIn('b.city_id', $data['cities']))
            ->when(!empty($data['bases']), fn($q) => $q->whereIn('b.id', $data['bases']))
            ->when(!empty($data['diagnostic_hypotheses']), fn($q) => $q->whereIn('fdh.diagnostic_hypothesis_id', $data['diagnostic_hypotheses']))
            ->when(!empty($data['groups_regional']), fn($q) => $q->whereIn('b.regional_group_id', $data['groups_regional']))
            ->when(!empty($data['units_destination']), function ($query) use ($data) {
                $query->where(function ($q) use ($data) {
                    $q->where(function ($q1) use ($data) {
                        $q1->where('a.attendable_type', 'secondary_attendance')
                            ->whereIn('sa.unit_destination_id', $data['units_destination']);
                    })->orWhere(function ($q2) use ($data) {
                        $q2->where('a.attendable_type', 'primary_attendance')
                            ->whereIn('pa.unit_destination_id', $data['units_destination']);
                    });
                });
            });

        $query->select([
            DB::raw('dh.name as diagnostic_hypothese'),
            DB::raw("COUNT(*) FILTER (WHERE a.attendable_type = 'primary_attendance') as primary_attendances"),
            DB::raw("COUNT(*) FILTER (WHERE a.attendable_type = 'secondary_attendance') as secondary_attendances"),
            DB::raw("COUNT(*) as total_attendances"),

            DB::raw("AVG(CASE
                WHEN a.attendable_type = 'primary_attendance'
                    AND ro.arrived_to_site_at IS NOT NULL
                    AND t.opening_at IS NOT NULL
                THEN EXTRACT(EPOCH FROM (ro.arrived_to_site_at - t.opening_at)) / 3600
                ELSE NULL
            END) as primary_attendances_average"),

            DB::raw("AVG(CASE
                    WHEN a.attendable_type = 'secondary_attendance'
                        AND ro.arrived_to_site_at IS NOT NULL
                        AND t.opening_at IS NOT NULL
                    THEN EXTRACT(EPOCH FROM (ro.arrived_to_site_at - t.opening_at)) / 3600
                    ELSE NULL
                END) as secondary_attendances_average"),

            DB::raw("(
                SUM(
                    CASE
                        WHEN a.attendable_type = 'primary_attendance' AND ro.arrived_to_site_at IS NOT NULL AND t.opening_at IS NOT NULL THEN EXTRACT(EPOCH FROM (ro.arrived_to_site_at - t.opening_at))
                        WHEN a.attendable_type = 'secondary_attendance' AND ro.arrived_to_site_at IS NOT NULL AND t.opening_at IS NOT NULL THEN EXTRACT(EPOCH FROM (ro.arrived_to_site_at - t.opening_at))
                        ELSE 0
                    END
                )
                / NULLIF(
                    COUNT(
                        CASE 
                            WHEN a.attendable_type IN ('primary_attendance', 'secondary_attendance')
                                AND (
                                    (a.attendable_type = 'primary_attendance' AND ro.arrived_to_site_at IS NOT NULL AND t.opening_at IS NOT NULL)
                                    OR
                                    (a.attendable_type = 'secondary_attendance' AND ro.arrived_to_site_at IS NOT NULL AND t.opening_at IS NOT NULL)
                                )
                            THEN 1 
                        END
                    ), 0
                ) / 3600
            ) as total_attendances_average")
        ])
            ->groupBy('dh.name')
            ->orderByDesc('total_attendances');

        $results = $request->get('list_all') ? $query->get() : $query->paginate($request->validated('per_page', 15));
        return AttendanceConsultationHdResource::collection($results);
    }
}
