<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlaceManagementResource extends JsonResource
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
            'name' => $this->name,
            'user_id' => $this->user_id,
            'urc_id' => $this->urc_id,
            'place_status_id' => $this->place_status_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'urgency_regulation_center' => new UrgencyRegulationCenterResource($this->whenLoaded('urgencyRegulationCenter')),
        ];
    }
}
