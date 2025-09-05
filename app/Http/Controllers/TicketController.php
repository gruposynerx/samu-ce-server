<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveTicketGeolocationRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Tickets', 'Gerecianemento de chamados')]
#[Subgroup('Geolocalização', 'Gerenciamento de localização')]
class TicketController extends Controller
{
    public function __construct(private TicketService $ticketService)
    {
    }

    /**
     * POST /api/{ticketId}/store-geolocation
     *
     * Salva o endereço de uma ocorrência
     */
    public function saveGeolocation(SaveTicketGeolocationRequest $request, string $ticketId): JsonResponse
    {
        $ticket = Ticket::findOrFail($ticketId);

        $this->ticketService->saveGeolocation($request->post('geolocation'), $ticket);
        $ticket->load('geolocation');

        return response()->json(new TicketResource($ticket));
    }
}
