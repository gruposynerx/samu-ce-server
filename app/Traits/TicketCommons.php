<?php

namespace App\Traits;

use App\Models\Requester;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;

trait TicketCommons
{
    public function storeTicket(array $data): Ticket
    {
        $requester = Requester::create($data['requester']);

        return Ticket::create([
            'requester_id' => $requester->id,
            'created_by' => auth()->user()->id,
            'receive_notification' => true,
            ...$data,
        ]);
    }

    public function defaultReturn(): JsonResponse
    {
        return response()->json(['message' => 'Chamado registrado com sucesso!']);
    }
}
