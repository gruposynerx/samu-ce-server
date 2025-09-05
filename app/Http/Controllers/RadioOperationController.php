<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatusEnum;
use App\Enums\PatrimonyStatusEnum;
use App\Enums\RadioOperationEventTypeEnum;
use App\Enums\RadioOperationFleetStatusEnum;
use App\Enums\VehicleStatusEnum;
use App\Events\RefreshAttendance\RefreshCancelAttendance;
use App\Exceptions\AttendanceException;
use App\Http\Requests\AttendancesByVehicleRequest;
use App\Http\Requests\IndexRadioOperationRequest;
use App\Http\Requests\RadioOperationRequest;
use App\Http\Requests\ShowByAttendanceRadioOperationRequest;
use App\Http\Requests\UpdateRadioOperationFleetRequest;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\RadioOperationResource;
use App\Models\Attendance;
use App\Models\PatrimonyRetainmentHistory;
use App\Models\PrimaryAttendance;
use App\Models\RadioOperation;
use App\Models\RadioOperationFleet;
use App\Models\RadioOperationFleet;
use App\Models\RadioOperationFleetHistory;
use App\Models\RadioOperationNote;
use App\Models\SecondaryAttendance;
use App\Models\VehicleStatusHistory;
use App\Scopes\UrcScope;
use App\Services\AttendanceService;
use App\Services\NotificationService;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Group;

#[Group(name: 'Rádio Operação', description: 'Seção responsável pela Rádio Operação')]
class RadioOperationController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly NotificationService $notificationService,
        private RadioOperationFleet $radioOperationFleet,
        private RadioOperation $radioOperation
    ) {
    }

    /**
     * GET api/radio-operation
     *
     * Retorna uma lista páginada de atendimentos, sendo possível filtrar por aguardando veículo, ou viatura enviada.
     */
    public function index(IndexRadioOperationRequest $request): ResourceCollection
    {
        $data = $request->validated();

        $results = Attendance::withoutGlobalScope(UrcScope::class)->with([
            'attendable' => function (MorphTo $query) {
                $query->morphWith([
                    SecondaryAttendance::class => [
                        'unitDestination:units.id,units.name',
                    ],
                    PrimaryAttendance::class => [
                        'unitDestination:id,name',
                    ],
                ]);
            },
            'latestUserAttendance' => function ($query) {
                $query->with('user:users.id,users.name')->whereHas('attendance', function ($query) {
                    $query->whereIn('attendance_status_id', AttendanceStatusEnum::ALL_STATUSES_IN_ATTENDANCE);
                });
            },
            'patient',
            'ticket.requester',
            'ticket.city:cities.id,cities.name',
            'latestMedicalRegulation',
            'radioOperation.vehicles.vehicleType',
            'radioOperation.vehicles.base' => fn ($q) => $q->select('id', 'city_id')->withoutGlobalScope(UrcScope::class),
            'radioOperation.vehicles.base.city',
            'radioOperation.fleets',
            'radioOperation.histories.creator:id,name',
            'latestMedicalRegulation.diagnosticHypotheses',
            'latestMedicalRegulation.createdBy:users.id,users.name',
            'sceneRecording:scene_recordings.attendance_id,scene_recordings.id,scene_recordings.diagnostic_hypothesis_id,scene_recordings.created_at,scene_recordings.created_by,scene_recordings.priority_type_id',
            'sceneRecording.latestDestinationUnitHistory:scene_recording_destination_unit_histories.id,scene_recording_destination_unit_histories.unit_destination_id,scene_recording_destination_unit_histories.scene_recording_id',
            'sceneRecording.latestDestinationUnitHistory.unitDestination:units.id,units.name',
            'sceneRecording.createdBy:users.id,users.name',
            'sceneRecording.diagnosticHypotheses',
        ])
            ->where('attendances.urc_id', auth()->user()->urc_id)
            ->join('tickets', function ($join) use ($data) {
                $join->on('attendances.ticket_id', '=', 'tickets.id')->when(!empty($data['ticket_type_id']), function ($query) use ($data) {
                    $query->where('tickets.ticket_type_id', $data['ticket_type_id']);
                });
            })
            ->leftJoin('scene_recordings', function ($join) {
                $join->on('attendances.id', '=', 'scene_recordings.attendance_id')
                    ->whereRaw('scene_recordings.id = (SELECT id FROM scene_recordings WHERE scene_recordings.attendance_id = attendances.id ORDER BY created_at DESC LIMIT 1)');
            })
            ->leftJoin('radio_operations', 'attendances.id', '=', 'radio_operations.attendance_id')
            ->when(!empty($data['filter_by_awaiting_vehicles']), function ($query) {
                $query->where(function ($query) {
                    $query->whereNull('radio_operations.vehicle_dispatched_at')
                        ->orWhereNull('radio_operations.id');
                })->whereNotIn('attendances.attendance_status_id', [...AttendanceStatusEnum::FINISHED_STATUSES]);
            })
            ->when(!empty($data['filter_by_vehicles_sent']), function ($query) {
                $query->where(function (Builder $query) {
                    $query->whereHas('radioOperation', function ($query) {
                        $query->whereNotNull('vehicle_dispatched_at');
                    })->where(function ($query) {
                        $query->whereNotIn('attendances.attendance_status_id', [...AttendanceStatusEnum::FINISHED_STATUSES])
                            ->orWhere(function ($query) {
                                $query->where('attendances.attendance_status_id', AttendanceStatusEnum::COMPLETED)
                                    ->where('attendances.urc_id', auth()->user()->urc_id)
                                    ->whereNotNull('vehicle_dispatched_at')
                                    ->whereNull('radio_operations.vehicle_released_at');
                            });
                    });
                });
            })
            ->when(!empty($data['search']), function ($query) use ($data) {
                $sequences = processSequences($data['search']);

                $query->where(function ($query) use ($sequences, $data) {
                    $query->whereHas('patient', function ($query) use ($data) {
                        $query->whereRaw('unaccent(patients.name) ilike unaccent(?)', "%{$data['search']}%");
                    })
                        ->orwhere('tickets.ticket_sequence_per_urgency_regulation_center', $sequences['ticketSequence'])
                        ->when(!empty($sequences['attendanceSequence']), function ($query) use ($sequences) {
                            $query->where('attendances.attendance_sequence_per_ticket', $sequences['attendanceSequence']);
                        });
                });
            })
            ->select('attendances.*')
            ->orderByRaw('COALESCE(scene_recordings.priority_type_id) DESC')
            ->orderByRaw(
                'CASE
                    WHEN attendances.attendance_status_id = ' . AttendanceStatusEnum::AWAITING_VEHICLE_COMMITMENT->value . ' THEN 1
                    WHEN attendances.attendance_status_id = ' . AttendanceStatusEnum::AWAITING_VACANCY->value . ' THEN 2
                    WHEN attendances.attendance_status_id = ' . AttendanceStatusEnum::AWAITING_RETURN->value . ' THEN 3
                    WHEN attendances.attendance_status_id = ' . AttendanceStatusEnum::AWAITING_MEDICAL_REGULATION->value . ' THEN 4
                END'
            )
            ->orderBy('tickets.opening_at')
            ->paginate(20);

        return AttendanceResource::collection($results);
    }

    /**
     * GET api/radio-operation/by-vehicle/{vehicleId}
     *
     * Retorna uma lista páginada de atendimentos vinculados com a rádio operação
     */
    public function getAttendancesByVehicle(string $vehicleId, AttendancesByVehicleRequest $request): ResourceCollection
    {
        $attendances = Attendance::with([
            'ticket:id,ticket_sequence_per_urgency_regulation_center',
            'patient',
            'latestMedicalRegulation:id,attendance_id,priority_type_id',
            'sceneRecording:id,attendance_id,priority_type_id',
            'radioOperation:id,attendance_id,vehicle_dispatched_at,vehicle_requested_at',
        ])
            ->withAggregate('radioOperation', 'vehicle_dispatched_at')
            ->withAggregate('latestMedicalRegulation', 'priority_type_id')
            ->whereRelation('radioOperation.vehicles', 'vehicles.id', $vehicleId)
            ->when($request->get('in_progress_attendance'),
                function ($query) {
                    $query->where(function (Builder $query) {
                        $query->whereHas('radioOperation', function ($query) {
                            $query->whereNotNull('vehicle_dispatched_at');
                        })->where(function ($query) {
                            $query->whereNotIn('attendances.attendance_status_id', [...AttendanceStatusEnum::FINISHED_STATUSES])
                                ->orWhere(function ($query) {
                                    $query->where('attendances.attendance_status_id', AttendanceStatusEnum::COMPLETED)
                                        ->where('attendances.urc_id', auth()->user()->urc_id)
                                        ->whereHas('radioOperation', function ($query) {
                                            $query->whereNotNull('vehicle_dispatched_at')
                                                ->whereNull('vehicle_released_at');
                                        });
                                });
                        });
                    });
                },
                function (Builder $query) {
                    $query
                        ->whereHas('radioOperation', fn ($q) => $q->whereNull('vehicle_dispatched_at'))
                        ->whereNotIn('attendance_status_id', [...AttendanceStatusEnum::FINISHED_STATUSES, AttendanceStatusEnum::AWAITING_RETURN]);
                }
            )
            ->orderByDesc('latest_medical_regulation_priority_type_id');

        return AttendanceResource::collection($attendances->paginate(8));
    }

    /**
     * GET api/radio-operation/show-by-attendance/{attendanceId}
     *
     * Retorna a rádio operação de um chamado específico.
     *
     * @urlParam attendanceId string required Id do chamado (tabela "attendances").
     */
    public function showByAttendance(ShowByAttendanceRadioOperationRequest $request, string $attendanceId): JsonResponse
    {
        $showAllFleetHistories = $request->validated('show_all_fleet_histories');

        $result = RadioOperation::with([
            'vehicles.vehicleType',
            'vehicles.base' => fn ($q) => $q->select('id', 'city_id')->withoutGlobalScope(UrcScope::class),
            'vehicles.base.city',
            'fleets.vehicle',
            'fleets.vehicle.vehicleType',
            'fleets.vehicle.latestVehicleStatusHistory:id,attendance_id,vehicle_id,vehicle_type_id,base_id,city_id',
            'fleets.vehicle.latestVehicleStatusHistory.vehicle:id,code',
            'fleets.vehicle.latestVehicleStatusHistory.vehicleType',
            'fleets.vehicle.latestVehicleStatusHistory.vehicleCity:id,name',
            'fleets.vehicle.base' => fn ($q) => $q->select('id', 'city_id')->withoutGlobalScope(UrcScope::class),
            'fleets.vehicle.base.city',
            'fleets.users:users.id,users.name',
            'fleets.externalProfessionals',
            'notes.patrimony',
            'attendance:id',
            'attendance.sceneRecording:id',
            'histories.creator:id,name',
            'histories.creator:id,name',
            'fleetHistories' => function ($query) use ($showAllFleetHistories) {
                if ($showAllFleetHistories) {
                    return $query;
                }

                return $query->limit(2)->latest();
            },
            'fleetHistories.creator:id,name',
        ])
            ->where('attendance_id', $attendanceId)->firstOrFail();

        return response()->json(new RadioOperationResource($result));
    }

    /**
     * POST api/radio-operation
     *
     * Cria um registro de rádio operação para o atendimento.
     */
    public function store(RadioOperationRequest $request): JsonResponse
    {
        $hasRadioOperation = RadioOperation::where('attendance_id', $request->get('attendance_id'))->exists();

        if ($hasRadioOperation) {
            throw ValidationException::withMessages([
                'attendance_id' => 'Já existe um registro de rádio operação para este atendimento.',
            ]);
        }

        $attendance = Attendance::find($request->get('attendance_id'));

        $hasNoVtrUsage = false;
        if ($request->has('radio_operation_fleet')) {
            foreach ($request->get('radio_operation_fleet') as $fleet) {
                if (isset($fleet['status']) && $fleet['status'] == RadioOperationFleetStatusEnum::MANUAL_REGISTRATION_NO_APP->value) {
                    $hasNoVtrUsage = true;
                    break;
                }
            }
        }

        $fleets = $request->get('radio_operation_fleet');

        if (!empty($request->get('vehicle_released_at')) && $attendance->sceneRecording()->doesntExist()) {
            throw ValidationException::withMessages([
                'vehicle_released_at' => 'É necessário o preenchimento do registro de cena para a liberação da VTR.',
            ]);
        }

        ['attendance' => $attendance, 'radio_operation' => $radioOperation] = DB::transaction(
            function () use (
                $request,
                $attendance,
                $fleets,
                $hasNoVtrUsage
            ) {
                $radioOperation = $attendance->radioOperation()->create([
                    ...$radioOperationData,
                    'created_by' => auth()->user()->id,
                ]);

                if ($request->has('radio_operation_fleet')) {
                    foreach ($fleets as $fleet) {
                        $storedFleet = $radioOperation->fleets()->create([
                            'vehicle_id' => $fleet['vehicle_id'],
                            'status' => $fleet['status'] ?? RadioOperationFleetStatusEnum::AWAITING_CONFIRMATION,
                        ]);

                        $users = Arr::map($fleet['users'], static fn ($user) => [
                            'id' => (string) Str::orderedUuid(),
                            'user_id' => $user['id'] ?? null,
                            'occupation_id' => $user['occupation_id'],
                            'external_professional' => $user['external_professional'] ?? null,
                        ]);

                        $storedFleet->users()->sync($users);
                    }
                }

                //TODO: REFACTOR EQUIPAMENTOS RETIDOS
                if ($request->has('notes')) {
                    $requestNotes = $request->get('notes')[0];

                    $createdNotes = $radioOperation->notes()->create([
                        ...$requestNotes,
                        'datetime' => $requestNotes['datetime'] ?? now(),
                        'responsible_professional' => $requestNotes['responsible_professional'] ?? auth()->user()->name,
                    ]);

                    $createdNotes->load('patrimony');

                    $createdNotes->where('patrimony_id', '!=', null)->each(function (RadioOperationNote $note) use ($attendance, $radioOperation) {
                        $patrimony = $note->patrimony;

                        PatrimonyRetainmentHistory::create([
                            'responsible_professional' => $note->responsible_professional,
                            'patrimony_id' => $patrimony->id,
                            'retained_at' => $note->datetime,
                            'retained_by' => auth()->id(),
                            'attendance_id' => $attendance->id,
                            'radio_operation_id' => $radioOperation->id,
                        ]);

                        $patrimony->update(['patrimony_status_id' => PatrimonyStatusEnum::RETAINED]);
                    });
                }

                if ($hasNoVtrUsage) {
                    $radioOperation->update([
                        'vehicle_dispatched_at' => now(),
                        'vehicle_confirmed_at' => now(),
                    ]);

                    $radioOperation->updateTimestamp(
                        RadioOperationEventTypeEnum::VEHICLE_DISPATCHED,
                        now(),
                        true,
                        auth()->id()
                    );

                    $updatedAttendance = $this->attendanceService->changeToVehicleDispatched($attendance, $radioOperation, 'created');

                    return [
                        'attendance' => $updatedAttendance,
                        'radio_operation' => $radioOperation->load('fleets', 'notes'),
                    ];
                }

                return [
                    'attendance' => $this->manageStatus($request, $attendance, $radioOperation, 'created'),
                    'radio_operation' => $radioOperation->load('fleets', 'notes'),
                ];
            }
        );

        $sentByApp = $request->has('sent_by_app') && $request->boolean('sent_by_app');

        if (!$sentByApp && $radioOperation->fleets()->exists()) {
            foreach ($radioOperation->fleets as $fleet) {
                try {
                    if ($fleet->status === RadioOperationFleetStatusEnum::MANUAL_REGISTRATION_NO_APP->value) {
                        continue;
                    }
                    $isAwaitingConfirmation = $fleet->status === RadioOperationFleetStatusEnum::AWAITING_CONFIRMATION->value;
                    $action = $isAwaitingConfirmation ? 'awaiting_confirmation' : 'created';

                    $this->notificationService->sendFleetNotification($fleet, $action, 0, false);
                } catch (\Exception $e) {
                    throw new \Exception('Erro ao enviar notificação para frota', 0, $e);
                }
            }
        }

        return response()->json([
            'radio_operation' => $radioOperation,
            'attendance' => $attendance,
        ]);
    }

    /**
     * PUT api/radio-operation/{id}
     *
     * Edita um registro de rádio operação do atendimento.
     *
     * @urlParam id string required ID do registro de rádio operação.
     */
    public function update(RadioOperationRequest $request, string $id): JsonResponse
    {
        $radioOperation = RadioOperation::findOrFail($id);

        Log::info('=== RADIO OPERATION ENCONTRADA ===', [
            'radio_operation_id' => $id,
            'current_data' => $radioOperation->toArray(),
        ]);

        $fleets = $request->get('radio_operation_fleet');

        if ($radioOperation->attendance->sceneRecording()->doesntExist() && !empty($request->get('vehicle_released_at'))) {
            throw ValidationException::withMessages([
                'vehicle_released_at' => 'É necessário o preenchimento do registro de cena para a liberação da VTR.',
            ]);
        }

        ['attendance' => $attendance, 'radio_operation' => $radioOperation] = DB::transaction(
            function () use ($request, $radioOperation, $radioOperationFleetStatus, $fleets) {
                $updateData = $request->except(['radio_operation_fleet', 'notes']);

                Log::info('=== DADOS PARA ATUALIZAÇÃO ===', [
                    'radio_operation_id' => $radioOperation->id,
                    'update_data' => $updateData,
                    'original_data' => $radioOperation->getOriginal(),
                ]);

                $radioOperation->fill($updateData);

                $latestLog = $radioOperation->attendance->latestLog;

                if ($latestLog && $latestLog->previous_attendance_status_id === AttendanceStatusEnum::COMPLETED->value) {
                    $sentByApp = $request->has('sent_by_app') && $request->boolean('sent_by_app');

                    if ($sentByApp) {
                        return [
                            'attendance' => false,
                            'radio_operation' => $radioOperation->load('fleets', 'notes'),
                        ];
                    }
                    
                    return [
                        'attendance' => $this->manageStatus(
                            $request,
                            $radioOperation->attendance,
                            $radioOperation,
                            'updated',
                            true
                        ),
                        'radio_operation' => $radioOperation->load('fleets', 'notes'),
                    ];
                }

                if ($request->has('radio_operation_fleet')) {
                    if ($radioOperation->attendance->sceneRecording()->exists()) {
                    } else {
                        $fleets = $request->get('radio_operation_fleet', []);
                        $isFleetChange = $this->isFleetChange($radioOperation, $fleets);

                        if ($isFleetChange) {
                            $radioOperation->fleets()->delete();
                            if (is_array($fleets)) {
                                foreach ($fleets as $fleet) {
                                    $storedFleet = $radioOperation->fleets()->create([
                                        'vehicle_id' => $fleet['vehicle_id'],
                                        'status' => $fleet['status'] ?? RadioOperationFleetStatusEnum::AWAITING_CONFIRMATION,
                                    ]);

<<<<<<< HEAD
                                    $users = Arr::map($fleet['users'], static fn ($user) => [
                                        'id' => (string) Str::orderedUuid(),
                                        'user_id' => $user['id'] ?? null,
                                        'occupation_id' => $user['occupation_id'],
                                        'external_professional' => $user['external_professional'] ?? null,
                                    ]);

                                    $storedFleet->users()->sync($users);
                                }
                            }
                        }
=======
                        $storedFleet->users()->sync($users);
>>>>>>> 86c150cf (rebase ajust)
                    }
                }

                if ($request->has('notes')) {
                    //TODO: REFACTOR EQUIPAMENTOS RETIDOS
                    //                    $radioOperation->notes()->delete();
                    //                    $attendance = $radioOperation->attendance;

                    $requestNotes = $request->get('notes')[0];

                    $radioOperation->notes()->create([
                        ...$requestNotes,
                        'datetime' => $requestNotes['datetime'] ?? now(),
                        'responsible_professional' => $requestNotes['responsible_professional'] ?? auth()->user()->name,
                    ]);

                    //                    $operationRetainments = PatrimonyRetainmentHistory::select(['id', 'patrimony_id', 'radio_operation_id', 'released_at'])
                    //                        ->where('radio_operation_id', $radioOperation->id)
                    //                        ->get();
                    //
                    //                    $retainmentsToRelease = $operationRetainments
                    //                        ->whereNull('released_at')
                    //                        ->whereNotIn('patrimony_id', $createdNotes->pluck('patrimony_id'));
                    //
                    //                    Patrimony::whereIn('id', $retainmentsToRelease->pluck('patrimony_id'))->update(['patrimony_status_id' => PatrimonyStatusEnum::AVAILABLE]);
                    //                    PatrimonyRetainmentHistory::whereIn('id', $retainmentsToRelease->pluck('id'))->update([
                    //                        'released_at' => now(),
                    //                        'released_by' => auth()->id(),
                    //                    ]);
                    //
                    //                    $alreadyRetained = $operationRetainments
                    //                        ->whereNull('released_at')
                    //                        ->keyBy('patrimony_id')
                    //                        ->toBase()
                    //                        ->except($retainmentsToRelease->pluck('patrimony_id')->toArray())
                    //                        ->pluck('patrimony_id')
                    //                        ->toArray();
                    //
                    //                    $createdNotes->keyBy('patrimony_id')
                    //                        ->whereNotNull('patrimony_id')
                    //                        ->keyBy('patrimony_id')
                    //                        ->toBase()
                    //                        ->except($alreadyRetained)
                    //                        ->each(function (RadioOperationNote $note) use ($attendance, $radioOperation) {
                    //                            $patrimony = $note->patrimony;
                    //
                    //                            PatrimonyRetainmentHistory::create([
                    //                                'responsible_professional' => $note->responsible_professional,
                    //                                'patrimony_id' => $patrimony->id,
                    //                                'retained_at' => $note->datetime,
                    //                                'retained_by' => auth()->id(),
                    //                                'attendance_id' => $attendance->id,
                    //                                'radio_operation_id' => $radioOperation->id,
                    //                            ]);
                    //
                    //                            $patrimony->update(['patrimony_status_id' => PatrimonyStatusEnum::RETAINED]);
                    //                        });
                }

                $attendance = $this->manageStatus($request, $radioOperation->attendance, $radioOperation, 'updated');

                return [
                    'attendance' => $attendance,
                    'radio_operation' => $radioOperation->load('fleets', 'notes'),
                ];
            }
        );

        $sentByApp = $request->has('sent_by_app') && $request->boolean('sent_by_app');

        return response()->json([
            'radio_operation' => $radioOperation->load('fleets', 'notes'),
            'attendance' => $attendance,
        ]);
    }

    /**
     * PUT api/radio-operation/{id}/update-fleet
     *
     * Vincula uma nova frota ao atendimento. A frota anterior é salva no histórico.
     * Sò pode ser feito caso: a viatura foi solicitada; a viatura não chegou ao local; o registro de cena não foi preenchido.
     *
     * @urlParam id string required ID do registro de rádio operação.
     */
    public function updateFleet(UpdateRadioOperationFleetRequest $request, string $id)
    {
        $radioOperation = RadioOperation::findOrFail($id);

        if (empty($radioOperation->vehicle_requested_at) && $radioOperation->fleets()->exists()) {
            throw ValidationException::withMessages(['id' => 'Não é possível editar a frota antes da solicitação da VTR.']);
        }

        if (!empty($radioOperation->arrived_to_site_at)) {
            throw ValidationException::withMessages(['id' => 'Não é possível editar a frota após a chegada ao local.']);
        }

        if ($radioOperation->attendance->sceneRecording()->exists()) {
            throw ValidationException::withMessages(['id' => 'Não é possível editar a frota após o registro de cena.']);
        }

        $data = $request->validated();

        $fleets = $request->get('radio_operation_fleet', []);

        DB::transaction(function () use ($radioOperation, $fleets, $data) {

            $histories = $radioOperation->fleets->map(function ($fleet) use ($data) {
                $fleet->load([
                    'vehicle.base:id,city_id',
                    'vehicle.base.city:id,name',
                ]);

                $users = $fleet->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'occupation_id' => $user->pivot->occupation_id,
                        'external_professional' => $user->pivot->external_professional ?? null,
                    ];
                })->toArray();

                $externalProfessionals = $fleet->externalProfessionals->map(function ($externalProfessional) {
                    return [
                        'occupation_id' => $externalProfessional->occupation_id,
                        'external_professional' => $externalProfessional->external_professional,
                    ];
                })->toArray();

                return [
                    'id' => Str::orderedUuid(),
                    'radio_operation_id' => $fleet->radio_operation_id,
                    'fleet' => json_encode([
                        'vehicle' => [
                            'id' => $fleet->vehicle->id,
                            'description' => $fleet->vehicle->description,
                            'code' => $fleet->vehicle->code,
                            'license_plate' => $fleet->vehicle->license_plate,
                            'vehicle_type' => $fleet->vehicle->vehicleType,
                            'base' => $fleet->vehicle->base,
                        ],
                        'users' => array_merge($users, $externalProfessionals),
                    ]),
                    'change_reason' => $data['change_reason'],
                    'previous_fleet_creator' => $fleet->created_by ?? auth()->id(),
                    'created_by' => auth()->user()->id,
                    'previous_vehicle_requested_at' => $fleet->radioOperation->vehicle_requested_at,
                    'previous_vehicle_dispatched_at' => $fleet->radioOperation->vehicle_dispatched_at,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            if (!empty($histories)) {
                RadioOperationFleetHistory::insert($histories);
            }

            $radioOperation->update([
                'vehicle_requested_at' => null,
                'vehicle_dispatched_at' => null,
            ]);
            $radioOperation->attendance->update(['attendance_status_id' => AttendanceStatusEnum::AWAITING_VEHICLE_COMMITMENT]);

            // TODO: provavelmente patrimônios retidos não vão funcionar mais depois disso aqui
            $radioOperation->vehicles->map(function ($vehicle) use ($radioOperation) {
                VehicleStatusHistory::create([
                    'vehicle_id' => $vehicle->id,
                    'vehicle_status_id' => VehicleStatusEnum::ACTIVE,
                    'attendance_id' => $radioOperation->attendance->id,
                ]);
            });

            $radioOperation->fleets()->delete();

            if (is_array($fleets)) {
                foreach ($fleets as $index => $fleet) {
                    $storedFleet = $radioOperation->fleets()->create([
                        'vehicle_id' => $fleet['vehicle_id'],
                        'status' => $fleet['status'] ?? RadioOperationFleetStatusEnum::AWAITING_CONFIRMATION,
                    ]);

                    $users = Arr::map($fleet['users'], static fn($user) => [
                        'id' => (string) Str::orderedUuid(),
                        'user_id' => $user['id'] ?? null,
                        'occupation_id' => $user['occupation_id'],
                        'external_professional' => $user['external_professional'] ?? null,
                    ]);

                    $storedFleet->users()->sync($users);

                    VehicleStatusHistory::create([
                        'vehicle_id' => $storedFleet->vehicle_id,
                        'vehicle_status_id' => VehicleStatusEnum::SOLICITED,
                        'attendance_id' => $radioOperation->attendance->id,
                    ]);
                }
            }
        });

        $radioOperation->load('fleets.users', 'attendance');

<<<<<<< HEAD
        $sentByApp = $request->has('sent_by_app') && $request->boolean('sent_by_app');

        if (!$sentByApp && $radioOperation->fleets()->exists()) {
            foreach ($radioOperation->fleets as $fleet) {
                if ($fleet->status === RadioOperationFleetStatusEnum::MANUAL_REGISTRATION_NO_APP->value) {
                    continue;
                }

                try {
                    $this->notificationService->sendFleetNotification($fleet, 'awaiting_confirmation', 0, false);
                } catch (\Exception $e) {
                    throw new \Exception('Erro ao enviar notificação para frota ' . $fleet->id, 0, $e);
                }
            }
        }

=======
>>>>>>> 86c150cf (rebase ajust)
        return response()->noContent();
    }

    /**
     * POST api/radio-operation/start-attendance/{id}
     *
     * Inicia uma rádio operação.
     *
     * @urlParam id string required ID do atendimento (primário ou secundário).
     *
     * @throws AttendanceException
     */
    public function start(string $id): JsonResponse
    {
        $attendance = Attendance::find($id);

        $isPending = $attendance->attendance_status_id === AttendanceStatusEnum::COMPLETED->value &&
            $attendance->radioOperation()->whereNull('vehicle_released_at')->exists();

        $attendance = $this->attendanceService->start($attendance, AttendanceStatusEnum::IN_ATTENDANCE_RADIO_OPERATION->value, $isPending);

        return response()->json($attendance);
    }

    /**
     * GET api/attendance/check/radio-operation/{id}
     *
     * Verifica se o atendimento está com o Rádio Operador em andamento, e se o usuário responsável é o mesmo que está logado.
     *
     * @urlParam id string required ID do atendimento (primário ou secundário).
     */
    public function check(string $id): JsonResponse
    {
        return $this->attendanceService->check($id, AttendanceStatusEnum::IN_ATTENDANCE_RADIO_OPERATION->value);
    }

    private function closeAttendance(Attendance $attendance): void
    {
        $allStatusesInAttendance = array_column(AttendanceStatusEnum::ALL_STATUSES_IN_ATTENDANCE, 'value');
        $inAttendance = in_array($attendance->attendance_status_id, $allStatusesInAttendance, true);

        if ($inAttendance) {
            $lastUserAttendance = $attendance->userAttendances()->latest()->first();

            if ($lastUserAttendance) {
                $attendance->update([
                    'attendance_status_id' => $lastUserAttendance->last_attendance_status_id,
                ]);

                RefreshCancelAttendance::dispatch($attendance->id);
            }
        }
    }

    private function manageStatus(FormRequest $request, Attendance $attendance, RadioOperation $radioOperation, string $eventStatus, bool $forceFinish = false): Attendance|false
    {
        if (AttendanceStatusEnum::tryFrom($attendance->latestLog->previous_attendance_status_id) === AttendanceStatusEnum::CONDUCT && empty($request->get('vehicle_released_at'))) {
            return $this->attendanceService->changeToConduct($attendance);
        }

        if (!empty($request->get('vehicle_released_at')) || $forceFinish) {
            $sentByApp = $request->has('sent_by_app') && $request->boolean('sent_by_app');

            if ($sentByApp) {
                return false;
            }

            return $this->attendanceService->changeToVehicleReleased($attendance, $radioOperation, $eventStatus);
        }

        if (!empty($request->get('arrived_to_destination_at'))) {
            return $this->attendanceService->changeToAwaitingConduct($attendance);
        }

        if (!empty($request->get('arrived_to_site_at'))) {
            return $this->attendanceService->changeToAwaitingConduct($attendance);
        }

        // Removido: changeToVehicleDispatched agora só acontece via confirmFleet
        // if (!empty($request->get('vehicle_dispatched_at'))) {
        //     return $this->attendanceService->changeToVehicleDispatched($attendance, $radioOperation, $eventStatus);
        // }

        if (!empty($request->get('left_from_site_at'))) {
            return $this->attendanceService->changeToConduct($attendance);
        }

        if (!empty($request->get('radio_operation_fleet')) && empty($request->get('vehicle_requested_at'))) {
<<<<<<< HEAD
            if ($radioOperation->attendance->sceneRecording()->exists()) {
                return false;
            }
=======
>>>>>>> 86c150cf (rebase ajust)
            return $this->attendanceService->changeToAwaitingVehicle($attendance, $radioOperation, $eventStatus);
        }

        if ($request->get('awaiting_fleet_confirmation') || !empty($request->get('vehicle_requested_at'))) {
<<<<<<< HEAD
            if ($radioOperation->attendance->sceneRecording()->exists()) {
                return false;
            }
=======
>>>>>>> 86c150cf (rebase ajust)
            return $this->attendanceService->changeToAwaitingVehicle($attendance, $radioOperation, $eventStatus);
        }

        return false;
    }

    public function confirmFleet($fleetId)
    {
        $fleet = $this->radioOperationFleet->findOrFail($fleetId);
        $radioOperation = $this->radioOperation->findOrFail($fleet->radio_operation_id);

        DB::beginTransaction();

        try {
            $fleet->update([
                'status' => RadioOperationFleetStatusEnum::CONFIRMED->value,
            ]);

            $radioOperation->update([
                'vehicle_dispatched_at' => now(),
                'vehicle_confirmed_at' => now(),
            ]);

            $radioOperation->updateTimestamp(
                RadioOperationEventTypeEnum::VEHICLE_DISPATCHED,
                now(),
                true,
                auth()->id()
            );

            $attendance = Attendance::withoutGlobalScope(UrcScope::class)
                ->findOrFail($radioOperation->attendance_id);
            $attendance->update([
                'attendance_status_id' => AttendanceStatusEnum::VEHICLE_SENT->value,
            ]);

            $this->notificationService->markNotificationAsResponded(
                auth()->id(),
                $fleetId,
                'radio_operation_fleet'
            );

            DB::commit();

            return response()->json([
                'message' => 'Frota confirmada com sucesso!',
                'fleet' => $fleet->fresh(),
                'attendance_status' => AttendanceStatusEnum::VEHICLE_SENT->value,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Erro ao confirmar a frota',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function markNotificationAsRead(Request $request): JsonResponse
    {
        $data = $request->validate([
            'reference_id' => 'required|string',
            'reference_type' => 'required|string',
        ]);

        app(NotificationService::class)->markNotificationAsRead(
            auth()->id(),
            $data['reference_id'],
            $data['reference_type']
        );

        return response()->json([
            'message' => 'Notificação marcada como lida com sucesso.',
        ]);
    }

    /**
     * Verifica se houve mudança real na composição da frota
     */
    private function isFleetChange(RadioOperation $radioOperation, array $newFleets): bool
    {
        $currentFleets = $radioOperation->fleets()->with('users')->get();

        if ($currentFleets->count() !== count($newFleets)) {
            return true;
        }

        foreach ($currentFleets as $index => $currentFleet) {
            if (!isset($newFleets[$index])) {
                return true;
            }

            $newFleet = $newFleets[$index];

            if ($currentFleet->vehicle_id !== $newFleet['vehicle_id']) {
                return true;
            }

            $currentUsers = $currentFleet->users->pluck('user_id')->sort()->values()->toArray();
            $newUsers = collect($newFleet['users'])->pluck('id')->sort()->values()->toArray();

            if ($currentUsers !== $newUsers) {
                return true;
            }

            $currentOccupations = $currentFleet->users->pluck('occupation_id')->sort()->values()->toArray();
            $newOccupations = collect($newFleet['users'])->pluck('occupation_id')->sort()->values()->toArray();

            if ($currentOccupations !== $newOccupations) {
                return true;
            }
        }

        return false;
    }
}
