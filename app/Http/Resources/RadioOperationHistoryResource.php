<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RadioOperationHistoryResource extends JsonResource
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
            'radio_operation_id' => $this->radio_operation_id,
            'event_type' => $this->event_type,
            'event_timestamp' => $this->event_timestamp,
            'sent_by_app' => $this->sent_by_app,
            'created_by_app' => $this->wasCreatedByApp(),
            'created_manually' => $this->wasCreatedManually(),
            'created_by' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ];
            }),
            'created_at' => $this->created_at,
            'radio_operation' => $this->whenLoaded('radioOperation', function () {
                return [
                    'id' => $this->radioOperation->id,
                    'attendance_id' => $this->radioOperation->attendance_id,
                ];
            }),
        ];
    }
}
