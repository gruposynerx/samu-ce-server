<?php

namespace App\Services;

use App\Entities\GoogleMaps\GeocodeResponse;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GoogleMapsService
{
    private PendingRequest $client;

    public function __construct()
    {
        $this->client = Http::baseUrl(config('external_services.google_maps_api_url'));
    }

    public function geocode(string $search)
    {
        $response = $this->client->get('/geocode/json', [
            'key' => config('external_services.google_maps_api_key'),
            'components' => 'country:BR',
            'address' => $search,
            'language' => 'pt-BR',
        ]);

        $results = collect($response->json(['results']));

        return $results->map(function ($result) {
            $components = collect($result['address_components']);

            return new GeocodeResponse([
                'address' => [
                    'street' => $components->firstWhere(fn ($component) => in_array('route', $component['types']))['long_name'] ?? '',
                    'state' => $components->firstWhere(fn ($component) => in_array('administrative_area_level_1', $component['types']))['long_name'] ?? '',
                    'city' => $components->firstWhere(fn ($component) => in_array('administrative_area_level_2', $component['types']))['long_name'] ?? '',
                    'neighborhood' => $components->firstWhere(fn ($component) => in_array('sublocality', $component['types']))['long_name'] ?? '',
                    'postal_code' => $components->firstWhere(fn ($component) => in_array('postal_code', $component['types']))['long_name'] ?? '',
                    'street_number' => $components->firstWhere(fn ($component) => in_array('street_number', $component['types']))['long_name'] ?? '',
                ],
                'place_id' => $result['place_id'],
                'geometry' => $result['geometry'],
                'formatted_address' => $result['formatted_address'],
            ]);
        });
    }
}
