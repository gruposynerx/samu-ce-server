<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RadioOperationResource extends JsonResource
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
            'attendance_id' => $this->attendance_id,
            'vehicle_requested_at' => $this->vehicle_requested_at,
            'vehicle_dispatched_at' => $this->vehicle_dispatched_at,
            'vehicle_confirmed_at' => $this->vehicle_confirmed_at,
            'vehicle_released_at' => $this->vehicle_released_at,
            'arrived_to_site_at' => $this->arrived_to_site_at,
            'left_from_site_at' => $this->left_from_site_at,
            'arrived_to_destination_at' => $this->arrived_to_destination_at,
            'release_from_destination_at' => $this->release_from_destination_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'created_by' => $this->created_by,
            'urc_id' => $this->urc_id,
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'council_number' => $this->creator->council_number ?? null,
                ];
            }),

            'vehicles' => $this->whenLoaded('vehicles'),
            'fleets' => $this->whenLoaded('fleets'),
            'notes' => $this->whenLoaded('notes'),
            'attendance' => $this->whenLoaded('attendance'),
            'fleet_histories' => $this->whenLoaded('fleetHistories'),

            'histories' => $this->whenLoaded('histories', function () {
                return $this->histories->map(function ($history) {
                    return [
                        'id' => $history->id,
                        'event_type' => $history->event_type,
                        'event_timestamp' => $history->event_timestamp,
                        'sent_by_app' => $history->sent_by_app,
                        'created_by' => $history->created_by,
                        'creator' => $history->creator ? [
                            'id' => $history->creator->id,
                            'name' => $history->creator->name,
                        ] : null,
                        'created_at' => $history->created_at,
                    ];
                });
            }),

            'timestamp_sources' => $this->when($this->relationLoaded('histories'), function () {
                $sources = [];

                foreach (['vehicle_requested_at', 'vehicle_dispatched_at', 'vehicle_confirmed_at',
                    'vehicle_released_at', 'arrived_to_site_at', 'left_from_site_at',
                    'arrived_to_destination_at', 'release_from_destination_at'] as $field) {

                    $eventType = str_replace('_at', '', $field);
                    $latestHistory = $this->histories
                        ->where('event_type', $eventType)
                        ->sortByDesc('event_timestamp')
                        ->first();

                    $sources[$field] = [
                        'sent_by_app' => $latestHistory?->sent_by_app ?? false,
                        'created_by' => $latestHistory?->created_by,
                        'creator_name' => $latestHistory?->creator?->name,
                        'last_updated' => $latestHistory?->event_timestamp,
                    ];
                }

                return $sources;
            }),
        ];
    }
}
