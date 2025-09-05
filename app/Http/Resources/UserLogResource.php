<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserLogResource extends JsonResource
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
            'urc_id' => $this->urc_id,
            'role_id' => $this->role_id,
            'logged_at' => $this->logged_at,
            'user_agent' => $this->user_agent,
            'longitude' => $this->longitude,
            'latitude' => $this->latitude,
            'urgency_regulation_center' => new UrgencyRegulationCenterResource($this->whenLoaded('urgencyRegulationCenter')),
            'role' => new RoleResource($this->whenLoaded('role')),
        ];
    }
}
