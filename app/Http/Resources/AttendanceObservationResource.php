<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceObservationResource extends JsonResource
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
            'observation' => $this->observation,
            'created_at' => $this->created_at,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'attendance' => new AttendanceResource($this->whenLoaded('attendance')),
            'role_creator' => new RoleResource($this->whenLoaded('roleCreator')),
        ];
    }
}
