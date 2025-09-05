<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PowerBIReportResource extends JsonResource
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
            'url' => $this->url,
            'description' => $this->description,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'urgency_regulation_center' => new UrgencyRegulationCenterResource($this->whenLoaded('urgencyRegulationCenter')),
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
        ];
    }
}
