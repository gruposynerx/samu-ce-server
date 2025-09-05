<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatusEnum;
use App\Exceptions\AttendanceException;
use App\Http\Requests\StoreSceneRecordingRequest;
use App\Http\Resources\SceneRecordingResource;
use App\Models\Attendance;
use App\Models\SceneRecording;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group(name: 'Registro de Cena', description: 'Seção responsável pela gestão do Registro de Cena')]
class SceneRecordingController extends Controller
{
    public function __construct(public AttendanceService $attendanceService)
    {
    }

    private function createRelations(SceneRecording $model, StoreSceneRecordingRequest $request): void
    {
        $relations = [
            'metrics',
            'wounds',
            'procedures',
            'medicines',
            'conducts',
            'antecedentsTypes',
        ];

        foreach ($relations as $relation) {
            if ($request->has($relation)) {
                $model->$relation()->createMany($request->get($relation));
            }
        }
    }

    /**
     * GET api/scene-recording/{id}
     *
     * Retorna o registro de cena.
     *
     * @urlParam id string required ID do registro de cena.
     */
    public function show(string $id): JsonResponse
    {
        $result = SceneRecording::with([
            'icd',
            'diagnosticHypotheses',
            'metrics',
            'wounds',
            'procedures.procedure',
            'medicines.medicine',
            'conducts',
            'destinationUnitHistories.unitDestination',
            'createdBy:id,name',
            'antecedentsTypes.antecedentType',
        ])->findOrFail($id);

        return response()->json(new SceneRecordingResource($result));
    }

    /**
     * POST api/scene-recording
     *
     * Realiza o cadastro de um registro de cena de um chamado específico.
     *
     * @throws AttendanceException
     */
    public function store(StoreSceneRecordingRequest $request): JsonResponse
    {
        $data = $request->validated();

        $attendance = Attendance::findOrFail($request->get('attendance_id'));

        $this->attendanceService->alreadyFinished($attendance->attendance_status_id);

        $radioOperation = $attendance->radioOperation;

        if ($radioOperation && !$radioOperation->vehicle_dispatched_at) {
            throw ValidationException::withMessages([
                'attendance_id' => 'O registro de cena só pode ser realizado após o envio da VTR.',
            ]);
        }

        $result = SceneRecording::create([
            ...$data,
            'created_by' => auth()->user()->id,
        ]);

        if ($request->has('patient')) {
            $patient = $attendance->patient();
            $patientData = $request->get('patient');

            if ($patient->exists()) {
                $patient->update($patientData);
            } else {
                $patient->create($patientData);
            }
        }

        if (!empty($data['unit_destination_id'])) {
            $result->destinationUnitHistories()->create([
                'unit_destination_id' => $data['unit_destination_id'],
                'created_by' => auth()->user()->id,
                'is_counter_reference' => false,
            ]);
        }
     
        if (!empty($data['diagnostic_hypotheses'][0]['diagnostic_hypothesis_id'])) {
            $result->diagnosticHypotheses()->sync(
                collect($data['diagnostic_hypotheses'])
                    ->mapWithKeys(fn ($diagnosticHypothesis, $index) => [
                        $index => [
                            'diagnostic_hypothesis_id' => $diagnosticHypothesis['diagnostic_hypothesis_id'],
                            'nature_type_id' => $diagnosticHypothesis['nature_type_id'],
                            'attendance_id' => $attendance->id,
                            'created_by' => auth()->id(),
                            'applied' => $diagnosticHypothesis['applied'] ?? null,
                            'recommended' => $diagnosticHypothesis['recommended'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    ])
                    ->toArray()
            );
        }

        $this->createRelations($result, $request);

        $nextStatus = $request->get('closed') ? AttendanceStatusEnum::COMPLETED->value : AttendanceStatusEnum::CONDUCT->value;

        $attendance->update(['attendance_status_id' => $nextStatus]);

        return response()->json(['message' => 'Registro de Cena registrado com sucesso!'], Response::HTTP_CREATED);
    }

    /**
     * POST api/scene-recording/start-attendance/{id}
     *
     * Inicia um registro de cena.
     *
     * @urlParam id string required ID do atendimento (primário ou secundário).
     *
     * @throws AttendanceException
     */
    public function start(string $id): JsonResponse
    {
        $attendance = Attendance::findOrFail($id)->load(['radioOperation']);
        $radioOperation = $attendance->radioOperation;

        if (!$radioOperation || !$radioOperation?->vehicle_dispatched_at) {
            throw ValidationException::withMessages([
                'attendance_id' => 'O registro de cena só pode ser realizado após o envio da VTR.',
            ]);
        }

        $attendance = $this->attendanceService->start($attendance, AttendanceStatusEnum::IN_ATTENDANCE_SCENE_RECORD->value);

        return response()->json($attendance);
    }

    /**
     * GET api/attendance/check/scene-recording/{id}
     *
     * Verifica se o atendimento está com o Registro de Cena em andamento, e se o usuário responsável é o mesmo que está logado.
     *
     * @urlParam id string required ID do atendimento (primário ou secundário).
     */
    public function check(string $id): JsonResponse
    {
        return $this->attendanceService->check($id, AttendanceStatusEnum::IN_ATTENDANCE_SCENE_RECORD->value);
    }
}
