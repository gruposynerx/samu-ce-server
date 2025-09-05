<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatusEnum;
use App\Events\RefreshAttendance\RefreshPrimaryAttendance;
use App\Events\RefreshAttendance\RefreshRadioOperation;
use App\Http\Requests\AttendanceIndexRequest;
use App\Http\Requests\ShowPrimaryOrSecondaryAttendanceRequest;
use App\Http\Requests\StorePrimaryAttendanceRequest;
use App\Http\Requests\UpdatePrimaryOrSecondaryAttendanceRequest;
use App\Http\Resources\PrimaryAttendanceResource;
use App\Models\Patient;
use App\Models\PrimaryAttendance;
use App\Services\AttendanceMonitoringService;
use App\Services\TicketService;
use App\Traits\TicketCommons;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group(name: 'Chamados', description: 'Seção responsável pela gestão de chamados')]
#[Subgroup(name: 'Ocorrência Primária', description: 'Seção responsável pela gestão de chamados do tipo "Ocorrência Primária"')]
class PrimaryAttendanceController extends Controller
{
    use TicketCommons;

    public function __construct(
        private TicketService $ticketService
    ) {
    }

    /**
     * GET api/ticket/primary-attendance
     *
     * Retorna uma lista páginada de atendimentos primários.
     */
    public function index(AttendanceIndexRequest $request): ResourceCollection
    {
        $data = $request->validated();
        $search = $request->validated('search');

        $results = PrimaryAttendance::with([
            'attendable.patient',
            'attendable.ticket.requester',
            'attendable.latestUserAttendance' => function ($query) {
                $query->with('user:users.id,users.name')->whereHas('attendance', function ($query) {
                    $query->whereIn('attendance_status_id', AttendanceStatusEnum::ALL_STATUSES_IN_ATTENDANCE);
                });
            },
            'attendable.ticket.city:cities.id,cities.name',
            'attendable.latestMedicalRegulation',
            'attendable.radioOperation.vehicles.vehicleType',
            'attendable.radioOperation.vehicles.base.city',
            'attendable.latestMedicalRegulation.diagnosticHypotheses',
            'attendable.latestMedicalRegulation.createdBy:users.id,users.name',
            'attendable.sceneRecording:scene_recordings.attendance_id,scene_recordings.id,scene_recordings.created_at,scene_recordings.created_by,scene_recordings.priority_type_id',
            'attendable.sceneRecording.latestDestinationUnitHistory:scene_recording_destination_unit_histories.id,scene_recording_destination_unit_histories.unit_destination_id,scene_recording_destination_unit_histories.scene_recording_id',
            'attendable.sceneRecording.latestDestinationUnitHistory.unitDestination:units.id,units.name',
            'attendable.sceneRecording.createdBy:users.id,users.name',
            'unitDestination:id,name',
            'attendable.sceneRecording.diagnosticHypotheses',
        ])
            ->join('attendances', 'primary_attendances.id', '=', 'attendances.attendable_id')
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
                        ->orWhereRaw('unaccent(primary_attendances.neighborhood) ilike unaccent(?)', "%{$search}%")
                        ->orwhere('tickets.ticket_sequence_per_urgency_regulation_center', $sequences['ticketSequence'])->when(!empty($sequences['attendanceSequence']), function ($query) use ($sequences) {
                            $query->where('attendances.attendance_sequence_per_ticket', $sequences['attendanceSequence']);
                        });
                });
            })
            ->when(!empty($data['attendance_status_id']), fn ($query) => $query->where('attendances.attendance_status_id', $data['attendance_status_id']))
            ->when(!empty($data['exclude_finished_attendances']), fn ($query) => $query->whereNotIn('attendances.attendance_status_id', AttendanceStatusEnum::FINISHED_STATUSES))
            ->orderByRaw('COALESCE(scene_recordings.priority_type_id, medical_regulations.priority_type_id) DESC')
            ->orderBy('tickets.opening_at')
            ->orderBy('attendances.attendance_sequence_per_ticket')
            ->select('primary_attendances.*')
            ->paginate(20);

        return PrimaryAttendanceResource::collection($results);
    }

    /**
     * GET api/ticket/primary-attendance/{id}
     *
     * Busca por um atendimento primário.
     *
     * @urlParam id string required ID do atendimento primário.
     */
    public function show(ShowPrimaryOrSecondaryAttendanceRequest $request, string $id): JsonResponse
    {
        $result = PrimaryAttendance::with([
            'attendable.patient:patients.id,patients.name,patients.gender_code,patients.age,patients.time_unit_id',
            'attendable.ticket.createdBy:users.id,users.name',
            'attendable.ticket.city:cities.id,cities.name',
            'attendable.ticket.requester:requesters.id,requesters.name,requesters.primary_phone,requesters.secondary_phone,requesters.requester_type_id,requesters.council_number',
            'attendable.firstMedicalRegulation:medical_regulations.id,medical_regulations.created_at,medical_regulations.attendance_id',
            'attendable.sceneRecording:scene_recordings.attendance_id,scene_recordings.id,scene_recordings.diagnostic_hypothesis_id,scene_recordings.created_at,scene_recordings.created_by',
            'attendable.sceneRecording.latestDestinationUnitHistory:scene_recording_destination_unit_histories.id,scene_recording_destination_unit_histories.unit_destination_id,scene_recording_destination_unit_histories.scene_recording_id',
            'attendable.sceneRecording.latestDestinationUnitHistory.unitDestination.city:cities.id,cities.name,cities.federal_unit_id',
            'attendable.sceneRecording.latestDestinationUnitHistory.unitDestination.city.federalUnit:federal_units.id,federal_units.uf',
            'attendable.sceneRecording.createdBy:users.id,users.name',
            'unitDestination:id,name',
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

        return response()->json(new PrimaryAttendanceResource($result));
    }

    /**
     * POST api/ticket/primary-attendance
     *
     * Realiza o cadastro de um chamado com o tipo "Ocorrência Primária".
     */
    public function store(StorePrimaryAttendanceRequest $request): JsonResponse
    {
        $data = $request->validated();

        $ticket = $this->storeTicket($data);
        $ticketId = $ticket['id'];

        $numberOfVictims = $data['number_of_victims'] ?? 1;
        $attendableIds = [];

        $attendanceMonitoring = config('app.frontend_url') . '/monitoramento-de-ocorrencia/acompanhamento/';

        $messageParameters = [];

        for ($index = 0; $index < $numberOfVictims; $index++) {
            $patientId = null;

            if ($data['patients'][$index] ?? false) {
                $patient = Patient::create($data['patients'][$index]);
                $patientId = $patient->id;
            }

            $formsSetting = auth()->user()->currentUrgencyRegulationCenter->formsSetting;
            $isLateOccurrence = $formsSetting->enable_late_occurrence ? $data['is_late_occurrence'] ?? false : false;

            $primaryAttendance = PrimaryAttendance::create($data);

            $primaryAttendance->attendable()->create([
                'patient_id' => $patientId,
                'created_by' => auth()->user()->id,
                'ticket_id' => $ticketId,
                'attendance_status_id' => AttendanceStatusEnum::AWAITING_MEDICAL_REGULATION->value,
                'is_late_occurrence' => $isLateOccurrence,
            ]);

            $attendable = $primaryAttendance->attendable;
            $attendableIds[] = $primaryAttendance->load('attendable')->attendable->id;

            $messageParameters[] = [
                'protocol' => "{$ticket->ticket_sequence_per_urgency_regulation_center}/{$attendable->attendance_sequence_per_ticket}",
                'attendance_id' => $attendable->id,
                'link' => "{$attendanceMonitoring}{$attendable->ticket_id}",
            ];

            if ($index === ($numberOfVictims - 1)) {
                RefreshPrimaryAttendance::dispatch($primaryAttendance, 'created');
                RefreshRadioOperation::dispatch(null, 'created');
            }
        }

        $requester = $ticket->requester;
        $primaryPhone = $requester->primary_phone ? removeCharacters($requester->primary_phone) : '';
        $secondaryPhone = $requester->secondary_phone ? removeCharacters($requester->secondary_phone) : '';

        $requesterParameters = [
            'phones' => ($primaryPhone && $secondaryPhone) ? [$primaryPhone, $secondaryPhone] : $primaryPhone,
            'name' => $requester->name,
        ];

        $monitoringSettingCurrentUrc = auth()->user()->currentUrgencyRegulationCenter->monitoringSetting;

        if ($monitoringSettingCurrentUrc->enable_attendance_monitoring) {
            (new AttendanceMonitoringService())->linkAttendance($requesterParameters, $messageParameters);
        }

        if ($request->has('geolocation')) {
            $this->ticketService->saveGeolocation($request->post('geolocation'), $ticket);
        }

        return response()->json($attendableIds);
    }

    /**
     * PUT api/ticket/primary-attendance/{id}
     *
     * Realiza a atualização de um chamado com o tipo "Ocorrência Primária".
     *
     * @urlParam id string required ID do atendimento primário.
     */
    public function update(string $id, UpdatePrimaryOrSecondaryAttendanceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $attendanceData = [
            'in_central_bed' => $data['in_central_bed'] ?? null,
            'protocol' => $data['protocol'] ?? null,
        ];

        $attendance = PrimaryAttendance::with('attendable.patient:patients.id,patients.name,patients.gender_code,patients.age,patients.time_unit_id')
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
