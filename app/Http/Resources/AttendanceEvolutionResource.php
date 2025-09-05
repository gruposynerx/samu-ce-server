<?php

namespace App\Http\Resources;

use App\Models\MedicalRegulation;
use App\Models\SceneRecording;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceEvolutionResource extends JsonResource
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
            'evolution' => $this->evolution,
            'created_at' => $this->created_at,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'attendance' => new AttendanceResource($this->whenLoaded('attendance')),
            'form' => match ($this->form_type) {
                getMorphAlias(MedicalRegulation::class) => new MedicalRegulationResource($this->whenLoaded('form')),
                getMorphAlias(SceneRecording::class) => new SceneRecordingResource($this->whenLoaded('form')),
            },
        ];
    }
}
