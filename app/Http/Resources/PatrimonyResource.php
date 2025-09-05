<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatrimonyResource extends JsonResource
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
            'patrimony_type_id' => $this->patrimony_type_id,
            'identifier' => $this->identifier,
            'patrimony_status_id' => $this->patrimony_status_id,
            'vehicle_id' => $this->vehicle_id,
            'urc_id' => $this->urc_id,
            'vehicle' => new VehicleResource($this->whenLoaded('vehicle')),
            'patrimony_type' => $this->whenLoaded('patrimonyType'),
        ];
    }
}
