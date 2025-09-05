<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UrgencyRegulationCenterResource extends JsonResource
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
            'city_id' => $this->city_id,
            'name' => $this->name,
            'street' => $this->street,
            'house_number' => $this->house_number,
            'neighborhood' => $this->neighborhood,
            'reference_place' => $this->reference_place,
            'is_active' => $this->is_active,
            'users' => UserResource::collection($this->whenLoaded('users')),
            'user_roles' => $this->whenLoaded('userRoles'),
        ];
    }
}
