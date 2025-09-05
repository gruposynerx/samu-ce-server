<?php

namespace App\Http\Controllers;

use App\Enums\ActionTypeEnum;
use App\Enums\AttendanceStatusEnum;
use App\Exceptions\AttendanceException;
use App\Http\Requests\StoreMedicalRegulationRequest;
use App\Http\Resources\MedicalRegulationResource;
use App\Models\Attendance;
use App\Models\MedicalRegulation;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Group;

#[Group(name: 'Regulação Médica', description: 'Seção responsável pela gestão da Regulação Médica')]
class MedicalRegulationController extends Controller
{
    public function __construct(public AttendanceService $attendanceService)
    {
    }

    private function defineNewStatus(int $actionType): int
    {
        return match ($actionType) {
            ActionTypeEnum::WITH_INTERVENTION->value => AttendanceStatusEnum::AWAITING_VEHICLE_COMMITMENT->value,
            ActionTypeEnum::WAITING_FOR_RETURN->value => AttendanceStatusEnum::AWAITING_RETURN->value,
            ActionTypeEnum::WAITING_FOR_VACANCY->value => AttendanceStatusEnum::AWAITING_VACANCY->value,
            default => AttendanceStatusEnum::COMPLETED->value,
        };
    }

    /**
     * GET api/medical-regulation/{attendanceId}
     *
     * Retorna uma lista de regulações médicas de um chamado específico
     *
     * @urlParam attendanceId string required Id do chamado (tabela "attendances").
     */
    public function index(string $attendanceId): ResourceCollection
    {
        $results = MedicalRegulation::with([
            'createdBy:id,name,council_number,cbo',
            'diagnosticHypotheses',
            'evolutions.creator:users.id,users.name',
        ])
            ->where('attendance_id', $attendanceId)
            ->orderByDesc('created_at')
            ->paginate(10);

        return MedicalRegulationResource::collection($results);
    }

    /**
     * POST api/medical-regulation
     *
     * Realiza o cadastro de uma regulação médica de um chamado específico.
     */
    public function store(StoreMedicalRegulationRequest $request): JsonResponse
    {
        $data = $request->validated();

        $medicalRegulation = MedicalRegulation::create([
            'created_by' => auth()->user()->id,
            ...$data,
        ]);

        $attendance = $medicalRegulation->attendance;

        $attendance->update([
            'attendance_status_id' => $this->defineNewStatus($data['action_type_id']),
        ]);

        $attendance->refresh();

        if (!empty($request->get('destination_unit_id'))) {
            $attendance->attendable()->update(['unit_destination_id' => $data['destination_unit_id']]);
        }

        if (!empty($data['diagnostic_hypotheses'])) {
            $medicalRegulation->diagnosticHypotheses()->sync(
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

        return response()->json(new MedicalRegulationResource($medicalRegulation));
    }

    /**
     * GET api/medical-regulation/latest/{attendanceId}
     *
     * Retorna a última regulação médica de um chamado específico
     *
     * @urlParam attendanceId string required Id do chamado (tabela "attendances").
     */
    public function latest(string $attendanceId): JsonResponse
    {
        $medicalRegulation = MedicalRegulation::with([
            'createdBy:id,name,council_number',
            'diagnosticHypotheses',
            'evolutions.creator:users.id,users.name',
        ])
            ->where('attendance_id', $attendanceId)
            ->orderBy('created_at', 'desc')
            ->firstOrFail();

        return response()->json(new MedicalRegulationResource($medicalRegulation));
    }

    /**
     * POST api/medical-regulation/start-attendance/{id}
     *
     * Inicia uma regulação médica.
     *
     * @urlParam id string required ID do atendimento (primário ou secundário).
     *
     * @throws AttendanceException
     */
    public function start(string $id): JsonResponse
    {
        $attendance = Attendance::find($id);
        $radioOperation = $attendance->radioOperation;

        if ($radioOperation && $radioOperation->vehicle_dispatched_at) {
            throw ValidationException::withMessages([
                'attendance_id' => 'Não é possível realizar a regulação médica de um chamado com a VTR enviada.',
            ]);
        }

        $attendance = $this->attendanceService->start($attendance, AttendanceStatusEnum::IN_ATTENDANCE_MEDICAL_REGULATION->value);

        return response()->json($attendance);
    }

    /**
     * GET api/attendance/check/medical-regulation/{id}
     *
     * Verifica se o atendimento está com a Regulação Médica em andamento, e se o usuário responsável é o mesmo que está logado.
     *
     * @urlParam id string required ID do atendimento (primário ou secundário).
     */
    public function check(string $id): JsonResponse
    {
        return $this->attendanceService->check($id, AttendanceStatusEnum::IN_ATTENDANCE_MEDICAL_REGULATION->value);
    }
}
