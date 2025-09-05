<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SceneRecordingDestinationUnitHistoryResource extends JsonResource
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
            'scene_recording_id' => $this->scene_recording_id,
            'unit_destination_id' => $this->unit_destination_id,
            'is_counter_reference' => $this->is_counter_reference,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'destination_unit_contact' => $this->destination_unit_contact,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'unit_destination' => new UnitResource($this->whenLoaded('unitDestination')),
            'scene_recording' => new SceneRecordingResource($this->whenLoaded('sceneRecording')),
        ];
    }
}
