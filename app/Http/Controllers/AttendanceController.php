<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatusEnum;
use App\Enums\RolesEnum;
use App\Enums\TicketTypeEnum;
use App\Events\RefreshAttendance\RefreshCancelAttendance;
use App\Http\Requests\AttendanceEqualsRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\PrimaryAttendance;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;
use Symfony\Component\HttpFoundation\Response;

#[Group(name: 'Chamados', description: 'Seção responsável pela gestão de chamados')]
#[Subgroup(name: 'Gestão de atendimento', description: 'Seção responsável por gerir o atendimento')]
class AttendanceController extends Controller
{
    /**
     * PUT api/attendance/{id}/close
     *
     * Fecha um atendimento que estava em andamento e volta ao status anterior.
     * Somente o usuário que iniciou o atendimento pode fechá-lo.
     *
     * @urlParam id string required ID do atendimento.
     */
    public function close(string $id): JsonResponse
    {
        $attendance = Attendance::findOrFail($id);
        $role = Role::find(auth()->user()->current_role);

        $lastUserAttendance = $attendance->userAttendances()->latest()->first();

        $allStatusesInAttendance = array_column(AttendanceStatusEnum::ALL_STATUSES_IN_ATTENDANCE, 'value');
        $inAttendance = in_array($attendance->attendance_status_id, $allStatusesInAttendance, true);

        $allowedRoles = array_column(RolesEnum::ALLLOWED_ROLES_TO_CLOSE_ATTENDANCE, 'value');
        $hasPermissionToCloseAttendance = in_array($role->name, $allowedRoles, true);

        if (!$inAttendance || !$hasPermissionToCloseAttendance && $lastUserAttendance?->user_id !== auth()->user()->id) {
            return response()->json([], Response::HTTP_FORBIDDEN);
        }

        $attendance->update([
            'attendance_status_id' => $lastUserAttendance?->last_attendance_status_id,
        ]);

        RefreshCancelAttendance::dispatch($id);

        return response()->json(['message' => 'Desistência do atendimento realizada com sucesso.']);
    }

    /**
     * GET api/attendance/equals
     *
     * Retorna o atendimento semelhante mais recente com base nos dados informados.
     */
    public function equals(AttendanceEqualsRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = Attendance::with([
            'attendable',
            'patient:patients.id,patients.name',
            'ticket:tickets.id,tickets.requester_id,tickets.city_id,tickets.ticket_sequence_per_urgency_regulation_center,tickets.ticket_type_id',
            'ticket.requester:requesters.id,requesters.name',
        ])
            ->where(function ($query) use ($data) {
                $isPrimaryOccurrence = (int) $data['ticket_type_id'] === TicketTypeEnum::PRIMARY_OCCURRENCE->value;

                $query
                    ->where(function ($query) use ($data) {
                        $query
                            ->whereHas('ticket.requester', function ($query) use ($data) {
                                $query->whereRaw("LOWER(unaccent(REPLACE(requesters.name, ' ', ''))) = LOWER(unaccent(?))", [str_replace(' ', '', $data['requester_name'])]);
                            })
                            ->orWhereHas('patient', function ($query) use ($data) {
                                $query
                                    ->where('patients.name', '!=', 'Vítima sem identificação')
                                    ->whereRaw("LOWER(unaccent(REPLACE(patients.name, ' ', ''))) = LOWER(unaccent(?))", [str_replace(' ', '', $data['patient_name'])]);
                            });
                    })
                    ->where(function ($query) use ($data, $isPrimaryOccurrence) {
                        $query
                            ->whereHas('ticket', function ($query) use ($data) {
                                $query->where('city_id', $data['city_id']);
                            })
                            ->when((!empty($data['neighborhood']) && !empty($data['street'])) && $isPrimaryOccurrence, function ($query) use ($data) {
                                $query->orWhereHasMorph('attendable', [app(PrimaryAttendance::class)->getMorphClass()], function ($query) use ($data) {
                                    $query->whereRaw("LOWER(unaccent(REPLACE(primary_attendances.neighborhood, ' ', ''))) = LOWER(unaccent(?))", [str_replace(' ', '', $data['neighborhood'])]);
                                });
                            });
                    })
                    ->when(($isPrimaryOccurrence && !empty($data['street'])), function ($query) use ($data) {
                        $query->orWhereHasMorph('attendable', [app(PrimaryAttendance::class)->getMorphClass()], function ($query) use ($data) {
                            $query->whereRaw("LOWER(unaccent(REPLACE(primary_attendances.street, ' ', ''))) = LOWER(unaccent(?))", [str_replace(' ', '', $data['street'])]);
                        });
                    });
            })
            ->whereHas('ticket', function ($query) use ($data) {
                $query->where('tickets.ticket_type_id', $data['ticket_type_id']);
            })
            ->whereNotIn('attendance_status_id', AttendanceStatusEnum::FINISHED_STATUSES)
            ->latest()
            ->firstOrFail();

        return response()->json(new AttendanceResource($result));
    }
}
