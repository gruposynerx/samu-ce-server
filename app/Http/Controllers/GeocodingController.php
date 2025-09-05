<?php

namespace App\Http\Controllers;

use App\Http\Requests\GeocodeRequest;
use App\Http\Requests\ReverseGeocodeRequest;
use App\Services\GoogleMapsService;
use App\Services\VehicleTrackingService;
use Illuminate\Http\JsonResponse;

class GeocodingController extends Controller
{
    public function __construct(
        private VehicleTrackingService $vehicleTrackingService,
        private GoogleMapsService $googleMapsService,
    ) {
    }

    public function geocode(GeocodeRequest $request)
    {
        return $this->googleMapsService->geocode($request->query('search'));
    }

    public function reverseGeocode(ReverseGeocodeRequest $request): JsonResponse
    {
        $data = $request->validated();

        return response()->json($this->vehicleTrackingService->reverseGeocode($data['latitude'], $data['longitude']));
    }
}
