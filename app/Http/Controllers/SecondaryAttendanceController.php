<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatusEnum;
use App\Http\Requests\AttendanceIndexRequest;
use App\Http\Requests\ShowPrimaryOrSecondaryAttendanceRequest;
use App\Http\Requests\StoreSecondaryAttendanceRequest;
use App\Http\Requests\UpdatePrimaryOrSecondaryAttendanceRequest;
use App\Http\Resources\SecondaryAttendanceResource;
use App\Models\Patient;
use App\Models\SecondaryAttendance;
use App\Traits\TicketCommons;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group(name: 'Chamados', description: 'Seção responsável pela gestão de chamados')]
#[Subgroup(name: 'Ocorrência Secundária', description: 'Seção responsável pela gestão de chamados do tipo "Ocorrência Secundária"')]
class SecondaryAttendanceController extends Controller
{
    use TicketCommons;

    /**
     * GET api/ticket/secondary-attendance
     *
     * Retorna uma lista páginada de atendimentos secundários.
     */
    public function index(AttendanceIndexRequest $request): ResourceCollection
    {
        $data = $request->validated();
        $search = $request->validated('search');

        $results = SecondaryAttendance::with([
            'unitOrigin:units.id,units.name,units.city_id',
            'unitOrigin.city:cities.id,cities.name',
            'unitDestination:units.id,units.name,units.city_id',
            'unitDestination.city:cities.id,cities.name',
            'attendable.ticket.requester',
            'attendable.ticket.city:cities.id,cities.name',
            'attendable.patient',
            'attendable.latestUserAttendance' => function ($query) {
                $query->with('user:users.id,users.name')->whereHas('attendance', function ($query) {
                    $query->whereIn('attendance_status_id', AttendanceStatusEnum::ALL_STATUSES_IN_ATTENDANCE);
                });
            },
            'attendable.latestMedicalRegulation',
            'attendable.latestMedicalRegulation.diagnosticHypotheses',
            'attendable.radioOperation.vehicles.vehicleType',
            'attendable.radioOperation.vehicles.base.city',
            'attendable.latestMedicalRegulation.createdBy:users.id,users.name',
            'attendable.sceneRecording:scene_recordings.attendance_id,scene_recordings.id,scene_recordings.diagnostic_hypothesis_id,scene_recordings.created_at,scene_recordings.created_by,scene_recordings.priority_type_id',
            'attendable.sceneRecording.latestDestinationUnitHistory:scene_recording_destination_unit_histories.id,scene_recording_destination_unit_histories.unit_destination_id,scene_recording_destination_unit_histories.scene_recording_id',
            'attendable.sceneRecording.latestDestinationUnitHistory.unitDestination:units.id,units.name',
            'attendable.sceneRecording.createdBy:users.id,users.name',
            'attendable.sceneRecording.diagnosticHypotheses',
        ])
            ->join('attendances', 'secondary_attendances.id', '=', 'attendances.attendable_id')
            ->join('tickets', 'attendances.ticket_id', '=', 'tickets.id')
            ->leftJoin('scene_recordings', function ($join) {
                $join->on('attendances.id', '=', 'scene_recordings.attendance_id')
                    ->whereRaw('scene_recordings.id = (SELECT id FROM scene_recordings WHERE scene_recordings.attendance_id = attendances.id ORDER BY created_at DESC LIMIT 1)');
            })
            ->leftJoin('medical_regulations', function ($join) {
                $join->on('attendances.id', '=', 'medical_regulations.attendance_id')
                    ->whereRaw('medical_regulations.id = (SELECT id FROM medical_regulations WHERE medical_regulations.attendance_id = attendances.id ORDER BY created_at DESC LIMIT 1)');
            })
            ->whereIn('attendances.attendance_status_id', AttendanceStatusEnum::INDEX_STATUSES)
            ->when(!empty($search), function ($query) use ($search) {
                $sequences = processSequences($search);

                $query->where(function ($query) use ($search, $sequences) {
                    $query->whereHas('attendable.patient', function ($query) use ($search) {
                        $query->whereRaw('unaccent(patients.name) ilike unaccent(?)', "%{$search}%");
                    })
                        ->orWhereHas('attendable.ticket.city', function ($query) use ($search) {
                            $query->whereRaw('unaccent(cities.name) ilike unaccent(?)', "%{$search}%");
                        })
                        ->orwhere('tickets.ticket_sequence_per_urgency_regulation_center', $sequences['ticketSequence'])
                        ->when(!empty($sequences['attendanceSequence']), function ($query) use ($sequences) {
                            $query->where('attendances.attendance_sequence_per_ticket', $sequences['attendanceSequence']);
                        });
                });
            })
            ->when(!empty($data['attendance_status_id']), fn ($query) => $query->where('attendances.attendance_status_id', $data['attendance_status_id']))
            ->when(!empty($data['exclude_finished_attendances']), fn ($query) => $query->whereNotIn('attendances.attendance_status_id', AttendanceStatusEnum::FINISHED_STATUSES))
            ->orderByRaw('COALESCE(scene_recordings.priority_type_id, medical_regulations.priority_type_id) DESC')
            ->orderBy('tickets.opening_at')
            ->select('secondary_attendances.*')
            ->paginate(20);

        return SecondaryAttendanceResource::collection($results);
    }

    /**
     * GET api/ticket/secondary-attendance/{id}
     *
     * Busca por um atendimento secundário.
     *
     * @urlParam id string required ID do atendimento secundário.
     */
    public function show(ShowPrimaryOrSecondaryAttendanceRequest $request, string $id): JsonResponse
    {
        $result = SecondaryAttendance::with([
            'unitOrigin.city:cities.id,cities.name,cities.federal_unit_id',
            'unitOrigin.city.federalUnit:federal_units.id,federal_units.uf',
            'unitDestination.city:cities.id,cities.name,cities.federal_unit_id',
            'unitDestination.city.federalUnit:federal_units.id,federal_units.uf',
            'attendable.radioOperation.vehicles',
            'attendable.ticket.city:cities.id,cities.name',
            'attendable.ticket.createdBy:users.id,users.name',
            'attendable.patient:patients.id,patients.name,patients.gender_code,patients.age,patients.time_unit_id',
            'attendable.ticket.requester:requesters.id,requesters.name,requesters.primary_phone,requesters.secondary_phone,requesters.requester_type_id,requesters.council_number',
            'attendable.sceneRecording:scene_recordings.attendance_id,scene_recordings.id,scene_recordings.diagnostic_hypothesis_id,scene_recordings.created_at,scene_recordings.created_by',
            'attendable.sceneRecording.latestDestinationUnitHistory:scene_recording_destination_unit_histories.id,scene_recording_destination_unit_histories.unit_destination_id,scene_recording_destination_unit_histories.scene_recording_id',
            'attendable.sceneRecording.latestDestinationUnitHistory.unitDestination.city:cities.id,cities.name,cities.federal_unit_id',
            'attendable.sceneRecording.latestDestinationUnitHistory.unitDestination.city.federalUnit:federal_units.id,federal_units.uf',
            'attendable.sceneRecording.createdBy:users.id,users.name',
            'attendable.medicalRegulations',
            'attendable.observations',
            'attendable.radioOperation.notes',
        ])
            ->when($request->has('with_latest_medical_regulation'), static function ($query) use ($request) {
                $query->with('attendable.latestMedicalRegulation')->when($request->has('with_diagnostic_hypotheses_latest_medical_regulation'), static function ($query) {
                    $query->with('attendable.latestMedicalRegulation.diagnosticHypotheses');
                });
            })
            ->when($request->has('with_latest_scene_recording'), static function ($query) use ($request) {
                $query->with('attendable.sceneRecording')->when($request->has('with_diagnostic_hypotheses_latest_scene_recording'), static function ($query) {
                    $query->with('attendable.sceneRecording.diagnosticHypotheses');
                });
            })
            ->whereHas('attendable', static function ($query) use ($id) {
                $query->where('attendances.id', $id);
            })
            ->firstOrFail();

        return response()->json(new SecondaryAttendanceResource($result));
    }

    /**
     * POST api/ticket/secondary-attendance
     *
     * Realiza o cadastro de um chamado com o tipo "Ocorrência Secundária".
     */
    public function store(StoreSecondaryAttendanceRequest $request): JsonResponse
    {
        $data = $request->validated();

        $ticketId = $this->storeTicket($data)['id'];
        $patientId = Patient::create($data['patients'][0])['id'];

        $formsSetting = auth()->user()->currentUrgencyRegulationCenter->formsSetting;
        $isLateOccurrence = $formsSetting->enable_late_occurrence ? $data['is_late_occurrence'] ?? false : false;

        $secondaryAttendance = SecondaryAttendance::create($data);

        $secondaryAttendance->attendable()->create([
            'patient_id' => $patientId,
            'ticket_id' => $ticketId,
            'created_by' => auth()->user()->id,
            'attendance_status_id' => AttendanceStatusEnum::AWAITING_MEDICAL_REGULATION->value,
            'is_late_occurrence' => $isLateOccurrence,
        ]);

        $attendableIds[] = $secondaryAttendance->load('attendable')->attendable->id;

        return response()->json($attendableIds);
    }

    /**
     * PUT api/ticket/secondary-attendance/{id}
     *
     * Realiza a atualização de um chamado com o tipo "Ocorrência Secundária".
     *
     * @urlParam id string required ID do atendimento secundário.
     */
    public function update(string $id, UpdatePrimaryOrSecondaryAttendanceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $attendanceData = [
            'in_central_bed' => $data['in_central_bed'] ?? null,
            'protocol' => $data['protocol'] ?? null,
        ];

        $attendance = SecondaryAttendance::with('attendable.patient:patients.id,patients.name,patients.gender_code,patients.age,patients.time_unit_id')
            ->whereHas('attendable', static function ($query) use ($id) {
                $query->where('attendances.id', $id);
            })
            ->firstOrFail();

        if ($data['in_central_bed'] !== $attendance->in_central_bed) {
            $attendance->update(['in_central_bed_updated_at' => now()]);
        }

        $attendance->update($attendanceData);
        $attendance->attendable->patient->update($data);

        return response()->json($attendance->fresh());
    }
}
