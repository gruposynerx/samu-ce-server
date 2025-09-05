<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatusEnum;
use App\Http\Requests\StoreAttendanceCancellationRecordRequest;
use App\Http\Resources\AttendanceCancellationRecordResource;
use App\Models\Attendance;
use App\Models\AttendanceCancellationRecord;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;
use Symfony\Component\HttpFoundation\Response;

#[Group(name: 'Chamados', description: 'Seção responsável pela gestão de chamados')]
#[Subgroup(name: 'Cancelamento de Atendimento', description: 'Seção responsável pelo registro de cancelamento do atendimento')]
class AttendanceCancellationRecordController extends Controller
{
    private array $allStatusesInAttendance;

    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->allStatusesInAttendance = array_column(AttendanceStatusEnum::ALL_STATUSES_IN_ATTENDANCE, 'value');
        $this->attendanceService = $attendanceService;
    }

    /**
     * POST api/attendance/cancellation
     *
     * Cria um registro de cancelamento para um atendimento.
     */
    public function store(StoreAttendanceCancellationRecordRequest $request): JsonResponse
    {
        $attendance = Attendance::findOrFail($request->get('attendance_id'));

        if (in_array($attendance->attendance_status_id, $this->allStatusesInAttendance, true)) {
            return response()->json(['message' => 'A Ocorrência não pode ser cancelada pois está em atendimento.'], Response::HTTP_FORBIDDEN);
        }

        $this->attendanceService->changeToCancelled($attendance);

        $result = AttendanceCancellationRecord::create($request->validated());

        return response()->json(new AttendanceCancellationRecordResource($result));
    }
}
