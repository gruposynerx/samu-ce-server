<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatusEnum;
use App\Http\Requests\StoreAttendanceLinkRequest;
use App\Models\Attendance;
use App\Models\AttendanceLink;
use App\Models\PrimaryAttendance;
use App\Models\SecondaryAttendance;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group(name: 'Chamados', description: 'Seção responsável pela gestão de chamados')]
#[Subgroup(name: 'Vínculos', description: 'Seção responsável por gerir os vínculos entre atendimentos')]
class AttendanceLinkController extends Controller
{
    /**
     * GET api/attendance/link/{attendanceId}
     *
     * Retorna uma lista de atendimentos vinculados a um atendimento.
     */
    public function index(string $attendanceId): JsonResponse
    {
        $results = Attendance::with([
            'childrenLinks.childrenLinks',
            'childrenLinks.childrenLinks.patient',
            'childrenLinks.childrenLinks.createdBy:users.id,users.name',
            'childrenLinks.childrenLinks.ticket.city:cities.id,cities.name',
            'childrenLinks.childrenLinks.ticket.createdBy:users.id,users.name',
            'childrenLinks.childrenLinks.ticket.requester:requesters.id,requesters.name,requesters.primary_phone,requesters.secondary_phone,requesters.requester_type_id',
            'childrenLinks.childrenLinks.ticket:tickets.id,tickets.ticket_sequence_per_urgency_regulation_center,tickets.opening_at,tickets.ticket_type_id,tickets.city_id,tickets.multiple_victims,tickets.number_of_victims,tickets.requester_id,tickets.created_by',
            'childrenLinks.patient',
            'childrenLinks.createdBy:users.id,users.name',
            'childrenLinks.ticket.city:cities.id,cities.name',
            'childrenLinks.ticket.createdBy:users.id,users.name',
            'childrenLinks.ticket.requester:requesters.id,requesters.name,requesters.primary_phone,requesters.secondary_phone,requesters.requester_type_id',
            'childrenLinks.ticket:tickets.id,tickets.ticket_sequence_per_urgency_regulation_center,tickets.opening_at,tickets.ticket_type_id,tickets.city_id,tickets.multiple_victims,tickets.number_of_victims,tickets.requester_id,tickets.created_by',
            'childrenLinks.attendable' => function (MorphTo $query) {
                $query->morphWith([
                    SecondaryAttendance::class => [
                        'unitOrigin',
                        'unitOrigin.city:cities.id,cities.name,cities.federal_unit_id',
                        'unitOrigin.city.federalUnit:federal_units.id,federal_units.uf',
                        'unitDestination',
                        'unitDestination.city:cities.id,cities.name,cities.federal_unit_id',
                        'unitDestination.city.federalUnit:federal_units.id,federal_units.uf',
                    ],
                    PrimaryAttendance::class => [
                        'unitDestination:id,name',
                    ],
                ]);
            },
            'childrenLinks.childrenLinks.attendable' => function (MorphTo $query) {
                $query->morphWith([
                    SecondaryAttendance::class => [
                        'unitOrigin',
                        'unitOrigin.city:cities.id,cities.name,cities.federal_unit_id',
                        'unitOrigin.city.federalUnit:federal_units.id,federal_units.uf',
                        'unitDestination',
                        'unitDestination.city:cities.id,cities.name,cities.federal_unit_id',
                        'unitDestination.city.federalUnit:federal_units.id,federal_units.uf',
                    ],
                    PrimaryAttendance::class => [
                        'unitDestination:id,name',
                    ],
                ]);
            },
        ])
            ->findOrFail($attendanceId)->childrenLinks;

        foreach ($results as $result) {
            if ($result->childrenLinks->count() > 0) {
                foreach ($result->childrenLinks as $childrenLink) {
                    $results->push($childrenLink);
                }
            }
        }

        return response()->json($results);
    }

    /**
     * POST api/attendance/link
     *
     * Realiza o cadastro de um ou mais vínculos entre dois atendimentos.
     */
    public function store(StoreAttendanceLinkRequest $request): JsonResponse
    {
        $data = $request->validated();

        foreach ($data['children_links'] as $childrenLinkId) {
            AttendanceLink::create([
                ...$data,
                'children_link_id' => $childrenLinkId,
                'created_by' => auth()->user()->id,
            ]);

            $childrenLink = Attendance::find($childrenLinkId);
            $childrenLink->update(['attendance_status_id' => AttendanceStatusEnum::LINKED->value]);
        }

        return response()->json(['message' => 'Vínculo(s) criado(s) com sucesso']);
    }
}
