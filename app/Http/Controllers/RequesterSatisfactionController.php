<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequesterSatisfactionRequest;
use App\Http\Resources\RequesterSatisfactionResource;
use App\Models\Attendance;
use App\Models\RequesterSatisfaction;
use App\Scopes\UrcScope;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;
use Symfony\Component\HttpFoundation\Response;

#[Group(name: 'Monitoramento de ocorrências', description: 'Gestão de ocorrências do SAMU')]
#[Subgroup(name: 'Pesquisa de Satisfação', description: 'Seção responsável pela gestão da pesquisa de satisfação do solicitante')]
class RequesterSatisfactionController extends Controller
{
    /**
     * POST api/attendance-monitoring/requester-satisfaction
     *
     * Realiza o cadastro de uma pesquisa de satisfação do solicitante.
     */
    public function store(StoreRequesterSatisfactionRequest $request): JsonResponse
    {
        $data = $request->validated();

        $requesterId = Attendance::withoutGlobalScope(UrcScope::class)->with([
            'ticket' => fn ($query) => $query->withoutGlobalScope(UrcScope::class),
        ])->findOrFail($data['attendance_id'])->ticket->requester_id;

        $result = RequesterSatisfaction::create([
            ...$data,
            'requester_id' => $requesterId,
        ]);

        return response()->json(new RequesterSatisfactionResource($result), Response::HTTP_CREATED);
    }
}
