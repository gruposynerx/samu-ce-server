<?php

namespace App\Http\Controllers;

use App\Http\Requests\FetchAllVehiclesCurrentLocation;
use App\Http\Requests\FetchVehicleHistoryRequest;
use App\Http\Requests\ReverseGeocodeRequest;
use App\Http\Requests\GetNearbyVehiclesRequest;
use App\Http\Resources\VehicleTrackingResource;
use App\Models\Vehicle;
use App\Services\VehicleTrackingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Group;

#[Group(name: 'Rastreamento de Veículos', description: 'Endpoints para rastreamento de veículos')]
class VehicleTrackingController extends Controller
{
    public function __construct(
        private VehicleTrackingService $vehicleTrackingService
    ) {
    }

    public function allVehiclesCurrentLocation(FetchAllVehiclesCurrentLocation $request): JsonResponse
    {
        $data = $request->validated();
        $vehicleStatusesToSearch = $data['vehicle_statuses_to_search'] ?? null;
        $search = $data['search'] ?? null;

        return response()->json($this->vehicleTrackingService->allVehiclesCurrentLocation($vehicleStatusesToSearch, $search));
    }

    public function fetchVehicleHistory(FetchVehicleHistoryRequest $request): JsonResponse
    {
        $data = $request->validated();

        $vehicle = Vehicle::findOrFail($data['vehicle_id']);

        $trackingData = $this->vehicleTrackingService->fetchVehicleHistory(
            $vehicle->tracking_system_id,
            Carbon::parse($data['start_date']),
            Carbon::parse($data['end_date']),
        );

        if ($trackingData->isEmpty()) {
            throw ValidationException::withMessages(['track_history' => 'Nenhum resultado encontrado.']);
        }

        $firstPosition = $trackingData->first();
        $lastPosition = $trackingData->last();

        $averageSpeed = $trackingData->sum('speed') / $trackingData->count();

        return response()->json([
            'vehicle' => $vehicle,
            'start_address' => [...$this->vehicleTrackingService->reverseGeocode($firstPosition['latitude'], $firstPosition['longitude']), 'datetime' => $firstPosition['registered_at']],
            'end_address' => [...$this->vehicleTrackingService->reverseGeocode($lastPosition['latitude'], $lastPosition['longitude']), 'datetime' => $lastPosition['registered_at']],
            'tracking_data' => $trackingData,
            'average_speed' => $averageSpeed,
        ]);
    }

    public function reverseGeocode(ReverseGeocodeRequest $request): JsonResponse
    {
        $data = $request->validated();

        return response()->json($this->vehicleTrackingService->reverseGeocode($data['latitude'], $data['longitude']));
    }

    public function getNearbyVehicles(GetNearbyVehiclesRequest $request): ResourceCollection
    {
        $data = $request->validated();

        $latitude = $data['latitude'];
        $longitude = $data['longitude'];

        $search = $data['search'] ?? null;
        $byDisponibility = filter_var($data['by_disponibility'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $nearbyVehicles = $this->vehicleTrackingService->getNearbyVehicles(
            (float) $latitude,
            (float) $longitude,
            $search,
            $byDisponibility
        );

        $nearbyVehicles->appends([
            'attendance_id' => $data['attendance_id'],
            'latitude' => $latitude,
            'longitude' => $longitude,
            'search' => $search,
            'by_disponibility' => $data['by_disponibility'] ?? false
        ]);

        return VehicleTrackingResource::collection($nearbyVehicles);
    }
}
