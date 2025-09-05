<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Model;

class TicketService
{
    public function saveGeolocation(array $address, Ticket $ticket): Model
    {
        return $ticket->geolocation()->updateOrCreate(['ticket_id' => $ticket->id], $address);
    }
}
