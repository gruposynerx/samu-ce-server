<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserScheduleSchemaResource extends JsonResource
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
            'user_id' => $this->user_id,
            'valid_from' => $this->valid_from,
            'valid_through' => $this->valid_through,
            'days_of_week' => $this->days_of_week,
            'clock_in' => $this->clock_in,
            'clock_out' => $this->clock_out,
            'schedule_type_id' => $this->schedule_type_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'schedule_type' => $this->whenLoaded('scheduleType'),
            'user' => new UserResource($this->whenLoaded('user')),
            'schedulable' => new WorkplaceResource($this->whenLoaded('schedulable')),
            'schedules' => UserScheduleResource::collection($this->whenLoaded('schedules')),
        ];
    }
}
