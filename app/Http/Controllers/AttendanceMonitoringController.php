<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShowAttendanceMonitoringRequest;
use App\Models\Ticket;
use App\Scopes\UrcScope;
use Exception;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;

#[Group(name: 'Monitoramento de ocorrências', description: 'Gestão de ocorrências do SAMU')]
class AttendanceMonitoringController extends Controller
{
    /**
     * GET api/attendance-monitoring
     *
     * Retorna uma ocorrência.
     *
     * @urlParam attendanceId string required ID do atendimento.
     */
    public function show(ShowAttendanceMonitoringRequest $request, string $ticketId): JsonResponse
    {
        $data = $request->validated();

        $ticket = Ticket::with([
            'attendances' => function ($query) {
                $query->withoutGlobalScopes()
                    ->whereHas('monitoring', function ($q) {
                        $q->whereNull('attendance_completed_at')
                            ->orWhereRaw("attendance_completed_at >= now() - interval '1 hour' * link_validation_time");
                    })->orderBy('attendance_sequence_per_ticket');
            },
            'attendances.requesterSatisfaction:id,attendance_id',
            'attendances.attendable' => fn ($query) => $query->withoutGlobalScopes(),
            'attendances.patient',
            'attendances.ticket' => fn ($query) => $query->withoutGlobalScope(UrcScope::class),
            'attendances.ticket.requester.city.federalUnit',
            'attendances.monitoring',
            'attendances.sceneRecording' => fn ($query) => $query->withoutGlobalScope(UrcScope::class)
                ->select('attendance_id', 'closed_justification'),
        ])->when(!empty($data['loading_requester_satisfaction']), fn ($q) => $q->with('attendance.requesterSatisfaction'))
            ->withoutGlobalScopes()
            ->findOrFail($ticketId);

        if ($ticket?->attendances->isEmpty()) {
            throw new Exception('Este link está expirado.');
        }

        return response()->json($ticket);
    }
}
