<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceObservationRequest;
use App\Http\Resources\AttendanceObservationResource;
use App\Models\AttendanceObservation;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group(name: 'Chamados', description: 'Seção responsável pela gestão de chamados')]
#[Subgroup(name: 'Observações do Atendimento', description: 'Seção responsável pela gestão de observações do atendimento')]
class AttendanceObservationController extends Controller
{
    /**
     * GET api/attendance/observation/{attendanceId}
     *
     * Retorna uma lista de observações de atendimento.
     */
    public function index(string $attendanceId): JsonResponse
    {
        $results = AttendanceObservation::with('creator:users.id,users.name', 'roleCreator:roles.id,roles.name')
            ->where('attendance_id', $attendanceId)
            ->get();

        return response()->json(AttendanceObservationResource::collection($results));
    }

    /**
     * POST api/attendance/observation
     *
     * Cria uma observação para um atendimento.
     */
    public function store(StoreAttendanceObservationRequest $request): JsonResponse
    {
        $result = AttendanceObservation::create($request->validated());

        return response()->json(new AttendanceObservationResource($result));
    }
}
