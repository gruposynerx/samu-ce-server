<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SceneRecordingMedicineResource extends JsonResource
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
            'medicine_id' => $this->medicine_id,
            'medicine_label' => $this->medicine?->name,
            'quantity' => $this->quantity,
            'observations' => $this->observations,
        ];
    }
}
