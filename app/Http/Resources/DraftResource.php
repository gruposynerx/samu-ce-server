<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DraftResource extends JsonResource
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
            'type' => $this->type,
            'urc_id' => $this->urc_id,
            'created_by' => $this->created_by,
            'fields' => $this->fields,
            'urgency_regulation_center' => new UrgencyRegulationCenterResource($this->whenLoaded('urgencyRegulationCenter')),
            'creator' => new UserResource($this->whenLoaded('creator')),
        ];
    }
}
