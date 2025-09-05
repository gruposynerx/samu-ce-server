<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
 public function toArray($request): array
{
    return [
        'id' => $this->id,
        'name' => $this->name,
        'start_time' => $this->start_time ? \Carbon\Carbon::parse($this->start_time)->format('H:i') : null,
        'end_time' => $this->end_time ? \Carbon\Carbon::parse($this->end_time)->format('H:i') : null,
        'next_day' => $this->next_day,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
    ];
}


}
