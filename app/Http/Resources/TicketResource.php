<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'multiple_victims' => $this->multiple_victims,
            'number_of_victims' => $this->number_of_victims,
            'ticket_sequence_per_urgency_regulation_center' => $this->ticket_sequence_per_urgency_regulation_center,
            'ticket_type_id' => $this->ticket_type_id,
            'opening_at' => $this->opening_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'city' => new CityResource($this->whenLoaded('city')),
            'requester' => $this->whenLoaded('requester'),
            'created_by' => $this->whenLoaded('createdBy'),
            'ticket_type' => $this->whenLoaded('ticketType'),
            'geolocation' => $this->whenLoaded('geolocation'),
            'urgency_regulation_center' => $this->whenLoaded('urgencyRegulationCenter'),
            'attendances' => AttendanceResource::collection($this->whenLoaded('attendances')),
        ];
    }
}
