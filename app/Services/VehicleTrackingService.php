<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Enums\VehicleStatusEnum;
use App\Scopes\UrcScope;
use Cache;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class VehicleTrackingService
{
    private Client $client;

    public function __construct(array $config = [])
    {
        $this->client = new Client([
            'base_uri' => config('external_services.rastro_system_api_url'),
        ]);

        $this->handleAuthentication($config);
    }

    private function handleAuthentication(array $user = []): void
    {
        $isSpecificUser = !empty($user['username']) || !empty($user['password']) || !empty($user['id']);

        $credentials = $isSpecificUser ? $user : [
            'id' => 'global',
            'username' => config('external_services.rastro_system_api_username'),
            'password' => config('external_services.rastro_system_api_password'),
        ];

        $cacheKey = 'rastro_system_api_token.' . $credentials['id'];

        $token = Cache::remember($cacheKey, now()->addHour(), function () use ($credentials) {
            $response = $this->client->post('login/', [
                'json' => [
                    'login' => $credentials['username'],
                    'senha' => $credentials['password'],
                    'app' => '1',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['token'];
        });

        $this->client = new Client([
            'base_uri' => config('external_services.rastro_system_api_url'),
            'headers' => [
                'Authorization' => "token $token",
            ],
        ]);
    }

    public function allVehiclesCurrentLocation($vehicleStatusesToSearch, $search): Collection
    {
        $response = $this->client->get('veiculos-v3/');
        $decodedResponse = json_decode($response->getBody()->getContents(), true);
        $rawTrackingData = collect($decodedResponse['dispositivos']);

        $idsList = $rawTrackingData->pluck('veiculo_id')->toArray();
        $chunkedIds = array_chunk($idsList, ceil(count($idsList) / 3));
        $vehicles = collect();

        foreach ($chunkedIds as $ids) {
            $partialVehicles = Vehicle::select('vehicles.id', 'code', 'tracking_device_imei', 'tracking_system_id', 'vehicles.base_id')
                ->with([
                    'vehicleType',
                    'base:id,vehicle_type_id,city_id',
                    'base.city:id,name',
                    'latestVehicleStatusHistory:id,vehicle_id,vehicle_status_id,attendance_id,description',
                    'latestVehicleStatusHistory.vehicleStatus',
                    'latestVehicleStatusHistory.attendance:id,ticket_id,attendance_sequence_per_ticket',
                    'latestVehicleStatusHistory.attendance.ticket:id,ticket_sequence_per_urgency_regulation_center',
                ])
                ->whereIn('tracking_system_id', $ids)
                ->when(isset($vehicleStatusesToSearch), function (Builder $query) use ($vehicleStatusesToSearch) {
                    $query->whereHas('latestVehicleStatusHistory', function (Builder $query) use ($vehicleStatusesToSearch) {
                        $query->whereIn('vehicle_status_id', $vehicleStatusesToSearch);
                    });
                })
                ->when(!empty($search), function (Builder $query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('vehicles.code', 'ilike', "%{$search}%")
                            ->orWhereRaw('unaccent(vehicles.license_plate) ilike unaccent(?)', "%{$search}%")
                            ->orWhereHas('base.city', fn ($query) => $query->whereRaw('unaccent(cities.name) ilike unaccent(?)', "%{$search}%"));
                    });
                })
                ->get();
            $vehicles = $vehicles->merge($partialVehicles);
        }
        $vehicles = $vehicles->keyBy('tracking_system_id');

        $trackingData = $rawTrackingData->map(function ($trackingData) use ($vehicles) {
            return [
                'tracking_system_id' => $trackingData['veiculo_id'] ?? null,
                'imei' => $trackingData['imei'] ?? null,
                'latitude' => $trackingData['latitude'] ?? null,
                'longitude' => $trackingData['longitude'] ?? null,
                'registered_at' => isset($trackingData['position_time']) ? Carbon::createFromFormat('d/m/Y H:i:s', $trackingData['position_time']) : null,
                'last_connection' => isset($trackingData['last_connection']) ? Carbon::createFromFormat('d/m/Y H:i:s', $trackingData['last_connection']) : null,
                'speed' => $trackingData['velocidade'] ?? null,
                'vehicle' => $vehicles[$trackingData['veiculo_id']] ?? null,
            ];
        })->filter(fn ($trackingData) => $trackingData['vehicle'] !== null)->values();

        return $trackingData;
    }

    public function fetchVehicleHistory(int $trackingSystemId, Carbon $start, Carbon $end): Collection
    {
        $payload = [
            'data_ini' => $start->format('d/m/Y'),
            'data_fim' => $end->format('d/m/Y'),
            'hora_ini' => $start->format('H:i:s'),
            'hora_fim' => $end->format('H:i:s'),
            'veiculo' => $trackingSystemId,
        ];

        $response = $this->client->post('veiculo/historico-intervalo/', [
            'json' => $payload,
        ]);

        $decodedResponse = json_decode($response->getBody()->getContents(), 'true');
        $rawTrackingData = collect($decodedResponse['veiculos']);

        $trackingData = $rawTrackingData->map(function ($trackingData) {
            return [
                'tracking_system_id' => $trackingData['veiculo_id'] ?? null,
                'position_id' => $trackingData['posicao_id'] ?? null,
                'latitude' => $trackingData['latitude'] ?? null,
                'longitude' => $trackingData['longitude'] ?? null,
                'registered_at' => isset($trackingData['server_time']) ? Carbon::createFromFormat('d/m/Y H:i:s', $trackingData['server_time']) : null,
                'last_updated_at' => isset($trackingData['later_server_time']) ? Carbon::createFromFormat('d/m/Y H:i:s', $trackingData['later_server_time']) : null,
                'stopped_period' => $trackingData['stopped_period'] ?? null,
                'speed' => $trackingData['velocidade'] ?? null,
                'action' => $trackingData['action'] ?? null,
                'imei' => $trackingData['unique_id'] ?? null,
            ];
        });

        return $trackingData;
    }

    public function reverseGeocode(float $latitude, float $longitude): array
    {
        $response = $this->client->post('endereco-com-lat-lng/', [
            'json' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
        ]);

        $decodedResponse = json_decode($response->getBody()->getContents(), true);

        return $decodedResponse['response'][0];
    }


    public function getNearbyVehicles(float $occurrenceLatitude, float $occurrenceLongitude, ?string $search = null, bool $byDisponibility = true)
    {
        $perPage = 10;
        $currentPage = request()->get('page', 1);

        $trackingData = $this->getTrackingDataOptimized($occurrenceLatitude, $occurrenceLongitude);

        $vehicleIdsQuery = Vehicle::select('id', 'tracking_system_id', 'base_id')
            ->where('disabled', false)
            ->availableForCurrentUrc();

        if ($search) {
            $vehicleIdsQuery = $this->applySearchFilters($vehicleIdsQuery, $search);
        }

        if ($byDisponibility) {
            $vehicleIdsQuery->whereHas('latestVehicleStatusHistory', function ($query) {
                $query->whereIn('vehicle_status_id', VehicleStatusEnum::ABLE_TO_OPERATION);
            });
        }

        $vehicleIds = $vehicleIdsQuery->pluck('id')->toArray();

        if (empty($vehicleIds)) {
            return new LengthAwarePaginator(
                collect([]),
                0,
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'pageName' => 'page']
            );
        }

        $vehiclesWithDistance = $this->processVehiclesInBatches($vehicleIds, $trackingData);
        $sortedVehicleIds = $this->sortVehiclesByPriority($vehiclesWithDistance);
        $totalCount = count($sortedVehicleIds);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedIds = array_slice($sortedVehicleIds, $offset, $perPage);
        $finalVehicles = $this->loadVehicleDetails($paginatedIds, $vehiclesWithDistance);

        return new LengthAwarePaginator(
            $finalVehicles,
            $totalCount,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    private function getTrackingDataOptimized(float $occurrenceLatitude, float $occurrenceLongitude): array
    {
        $trackingData = [];

        try {
            $response = $this->client->get('veiculos-v3/');
            $decodedResponse = json_decode($response->getBody()->getContents(), true);

            if (isset($decodedResponse['dispositivos'])) {
                foreach ($decodedResponse['dispositivos'] as $device) {
                    if (
                        isset($device['veiculo_id'], $device['latitude'], $device['longitude']) &&
                        $device['latitude'] && $device['longitude']
                    ) {

                        $distance = $this->calculateDistance(
                            $occurrenceLatitude,
                            $occurrenceLongitude,
                            $device['latitude'],
                            $device['longitude']
                        );

                        $trackingData[$device['veiculo_id']] = [
                            'distance_km' => round($distance, 2),
                            'latitude' => $device['latitude'],
                            'longitude' => $device['longitude'],
                            'speed' => $device['velocidade'] ?? null,
                            'registered_at' => isset($device['position_time']) ?
                                Carbon::createFromFormat('d/m/Y H:i:s', $device['position_time']) : null,
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados de rastreamento:', ['exception' => $e]);
        }

        return $trackingData;
    }

    private function applySearchFilters($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('code', 'ilike', "%{$search}%")
                ->orWhereRaw('unaccent(chassis) ilike unaccent(?)', "%{$search}%")
                ->orWhereRaw('unaccent(license_plate) ilike unaccent(?)', "%{$search}%")
                ->orWhereHas('base', function ($query) use ($search) {
                    $query->withoutGlobalScope(UrcScope::class)
                        ->whereRaw('unaccent(name) ilike unaccent(?)', "%{$search}%")
                        ->orWhereHas('city', fn($query) => $query->whereRaw('unaccent(cities.name) ilike unaccent(?)', "%{$search}%"));
                })
                ->orWhereHas('vehicleType', function ($query) use ($search) {
                    $query->whereRaw('unaccent(vehicle_types.name) ilike unaccent(?)', "%{$search}%")
                        ->orWhereRaw('unaccent(code) ilike unaccent(?)', "%{$search}%");
                })
                ->orWhereHas('latestVehicleStatusHistory.vehicleStatus', function ($query) use ($search) {
                    $query->whereRaw('unaccent(vehicle_statuses.name) ilike unaccent(?)', "%{$search}%");
                });
        });
    }

    private function processVehiclesInBatches(array $vehicleIds, array $trackingData): array
    {
        $batchSize = 50;
        $vehiclesWithDistance = [];

        $batches = array_chunk($vehicleIds, $batchSize);

        foreach ($batches as $batch) {
            $vehicles = Vehicle::select('id', 'tracking_system_id', 'base_id')
                ->with('latestVehicleStatusHistory:id,vehicle_status_id,vehicle_id')
                ->whereIn('id', $batch)
                ->get();

            foreach ($vehicles as $vehicle) {
                $trackingInfo = $trackingData[$vehicle->tracking_system_id] ?? null;
                $hasTracking = $trackingInfo !== null;

                $vehiclesWithDistance[$vehicle->id] = [
                    'id' => $vehicle->id,
                    'tracking_system_id' => $vehicle->tracking_system_id,
                    'base_id' => $vehicle->base_id,
                    'distance_km' => $hasTracking ? $trackingInfo['distance_km'] : null,
                    'has_tracking' => $hasTracking ? 1 : 0,
                    'has_base' => $vehicle->base_id ? 1 : 0,
                    'vehicle_status_id' => $vehicle->latestVehicleStatusHistory?->vehicle_status_id,
                    'tracking_info' => $trackingInfo
                ];
            }
            unset($vehicles);
        }

        return $vehiclesWithDistance;
    }

    private function sortVehiclesByPriority(array $vehiclesWithDistance): array
    {
        $collection = collect($vehiclesWithDistance);

        $sorted = $collection->sortBy([
            ['has_base', 'desc'],
            ['has_tracking', 'desc'],
            ['distance_km', 'asc'],
            function ($vehicle) {
                return $this->getStatusPriority($vehicle['vehicle_status_id']);
            },
            ['id', 'asc']
        ]);

        return $sorted->pluck('id')->toArray();
    }

    private function loadVehicleDetails(array $vehicleIds, array $vehiclesWithDistance): \Illuminate\Support\Collection
    {
        if (empty($vehicleIds)) {
            return collect([]);
        }

        $vehicles = Vehicle::with([
            'latestVehicleStatusHistory:id,vehicle_status_id,vehicle_id,description',
            'latestVehicleStatusHistory.vehicleStatus',
            'vehicleType',
            'base' => function ($builder) {
                $builder->withoutGlobalScope(UrcScope::class);
            },
            'base.city',
        ])
            ->whereIn('id', $vehicleIds)
            ->get()
            ->keyBy('id');

        $orderedVehicles = collect();

        foreach ($vehicleIds as $vehicleId) {
            if (isset($vehicles[$vehicleId])) {
                $vehicle = $vehicles[$vehicleId];
                $vehicleData = $vehiclesWithDistance[$vehicleId];

                $vehicle->distance_km = $vehicleData['distance_km'];
                $vehicle->last_position = $vehicleData['tracking_info'] ? [
                    'latitude' => $vehicleData['tracking_info']['latitude'],
                    'longitude' => $vehicleData['tracking_info']['longitude'],
                    'registered_at' => $vehicleData['tracking_info']['registered_at'],
                ] : null;
                $vehicle->has_tracking = $vehicleData['has_tracking'];

                $orderedVehicles->push($vehicle);
            }
        }

        return $orderedVehicles;
    }

    private function getStatusPriority(?int $vehicleStatusId): ?int
    {
        $priorities = [
            VehicleStatusEnum::ACTIVE->value => 0,
            VehicleStatusEnum::SOLICITED->value => 1,
            VehicleStatusEnum::COMMITTED->value => 2,
        ];

        return $priorities[$vehicleStatusId] ?? null;
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
