<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SceneRecordingProcedureResource extends JsonResource
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
            'procedure_code' => $this->procedure_code,
            'procedure_label' => $this->procedure?->name,
            'observations' => $this->observations,
        ];
    }
}
