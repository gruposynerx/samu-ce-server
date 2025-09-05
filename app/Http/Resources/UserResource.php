<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'national_health_card' => $this->national_health_card,
            'identifier' => $this->identifier,
            'email' => $this->email,
            'birthdate' => $this->birthdate,
            'gender_code' => $this->gender_code,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'neighborhood' => $this->neighborhood,
            'street' => $this->street,
            'street_type' => $this->street_type,
            'house_number' => (string) $this->house_number,
            'complement' => $this->complement,
            'council_number' => $this->council_number,
            'cbo' => $this->cbo,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'city_id' => $this->city_id,
            'urc_id' => $this->urc_id,
            'mobile_access' => $this->mobile_access,
            'last_modified_mobile_access_user_id' => $this->last_modified_mobile_access_user_id,
            'has_power_bi' => $this->whenNotNull($this->hasPowerBI),
            'city' => new CityResource($this->whenLoaded('city')),
            'roles' => RoleResource::collection($this->whenLoaded('urcRoles')),
            'urgency_regulation_centers' => UrgencyRegulationCenterResource::collection($this->whenLoaded('urgencyRegulationCenters')),
            'current_role' => new RoleResource($this->whenLoaded('currentRole')),
            'current_urgency_regulation_center' => new UrgencyRegulationCenterResource($this->whenLoaded('currentUrgencyRegulationCenter')),
            'last_password_history' => new UserPasswordHistoryResource($this->whenLoaded('lastPasswordHistory')),
            'occupation' => new OccupationResource($this->whenLoaded('occupation')),
            'latest_user_status_history' => new UserStatusHistoryResource($this->whenLoaded('latestStatusesHistory')),
            'place' => new PlaceManagementResource($this->whenLoaded('place')),
            'schedules' => UserScheduleResource::collection($this->whenLoaded('schedules')),
            'schedule_schemas' => UserScheduleSchemaResource::collection($this->whenLoaded('schedulesSchemas')),
        ];
    }
}
