<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserScheduleResource extends JsonResource
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
            'schema_id' => $this->schema_id,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'occupation_code' => $this->occupation_code,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'schema' => new UserScheduleSchemaResource($this->whenLoaded('schema')),
            'schedulables' => new WorkplaceResource($this->whenLoaded('schedulables')),
            'position_job' => new PositionJobResource($this->whenLoaded('positionJobs')),
        ];
    }
}
