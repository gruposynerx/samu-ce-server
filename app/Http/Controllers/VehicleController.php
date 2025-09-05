<?php

namespace App\Http\Controllers;

use App\Enums\VehicleStatusEnum;
use App\Http\Requests\IndexVehicleRequest;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Models\Base;
use App\Models\Vehicle;
use App\Scopes\UrcScope;
use App\Services\VehicleService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Group;

#[Group(name: 'Viaturas', description: 'Seção responsável pela gestão de viaturas')]
class VehicleController extends Controller
{
    /**
     * GET api/vehicles
     *
     * Retorna uma lista páginada de viaturas.
     */
    public function index(IndexVehicleRequest $request): ResourceCollection
    {
        $search = $request->validated('search');

        $latestStatusSubQuery = DB::table('vehicle_status_histories')
            ->select('vehicle_id', DB::raw('MAX(id) as max_id'))
            ->groupBy('vehicle_id');

        $results = Vehicle::with([
            'latestVehicleStatusHistory:id,vehicle_status_id,vehicle_id,description',
            'latestVehicleStatusHistory.vehicleStatus',
            'vehicleType',
            'patrimonies',
            'patrimonies.patrimonyType',
            'base' => function ($builder) {
                $builder->withoutGlobalScope(UrcScope::class);
            },
            'base.city',
        ])->select('vehicles.*')
            ->joinSub($latestStatusSubQuery, 'latest_status_sub', function ($join) {
                $join->on('vehicles.id', '=', 'latest_status_sub.vehicle_id');
            })
            ->join('vehicle_status_histories as latest_status', 'latest_status.id', '=', 'latest_status_sub.max_id')
            ->join('vehicle_statuses', 'latest_status.vehicle_status_id', '=', 'vehicle_statuses.id')
            ->when($search ?? null, function ($query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('code', 'ilike', "%{$search}%")
                        ->orWhereRaw('unaccent(chassis) ilike unaccent(?)', "%{$search}%")
                        ->orWhereRaw('unaccent(license_plate) ilike unaccent(?)', "%{$search}%")
                        ->orWhereHas('base', function ($query) use ($search) {
                            $query->withoutGlobalScope(UrcScope::class)
                                ->whereRaw('unaccent(name) ilike unaccent(?)', "%{$search}%")
                                ->orWhereHas('city', fn ($query) => $query->whereRaw('unaccent(cities.name) ilike unaccent(?)', "%{$search}%"));
                        })
                        ->orWhereHas('vehicleType', function ($query) use ($search) {
                            $query->whereRaw('unaccent(vehicle_types.name) ilike unaccent(?)', "%{$search}%")
                                ->orWhereRaw('unaccent(code) ilike unaccent(?)', "%{$search}%");
                        })
                        ->orWhereRaw('unaccent(vehicle_statuses.name) ilike unaccent(?)', "%{$search}%");
                });
            })
            ->when($request->has('by_disponibility'), function ($query) {
                $order = Arr::map(VehicleStatusEnum::ABLE_TO_OPERATION, function ($status, $key) {
                    return "WHEN {$status->value} THEN $key";
                });

                $query->join('vehicle_status_histories', 'vehicle_status_histories.id', '=', 'latest_status_sub.max_id')
                    ->rightJoin('bases', 'vehicles.base_id', '=', 'bases.id')
                    ->leftJoin('cities', 'bases.city_id', '=', 'cities.id')
                    ->leftJoin('vehicle_types', 'bases.vehicle_type_id', '=', 'vehicle_types.id')
                    ->select('vehicles.*', 'vehicle_status_histories.vehicle_status_id', 'cities', 'vehicle_types')
                    ->whereIn('vehicle_status_histories.vehicle_status_id', VehicleStatusEnum::ABLE_TO_OPERATION)
                    ->orderByRaw('CASE vehicle_status_histories.vehicle_status_id ' . implode(' ', $order) . ' END, cities.name, vehicle_types.name, vehicles.code');
            })
            ->when($request->has('only_trackable'), function ($query) {
                $query->whereNotNull('tracking_system_id')
                    ->whereNotNull('tracking_device_imei');
            })
            ->when(!$request->has('show_all'), function (Builder $query) {
                $query->availableForCurrentUrc();
            })
            ->paginate(10);

        return VehicleResource::collection($results);
    }

    /**
     * GET api/vehicles/{id}
     *
     * Retorna uma viatura específica e sua equipe.
     */
    public function show(string $id): JsonResponse
    {
        $result = Vehicle::with('latestVehicleStatusHistory:id,vehicle_status_id,vehicle_id,description')->findOrFail($id);

        return response()->json(new VehicleResource($result));
    }

    /**
     * POST api/vehicles
     *
     * Realiza o cadastro de uma viatura.
     */
    public function store(StoreVehicleRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($data['base_id']) {
            $validation = Vehicle::where('base_id', $data['base_id'])
                ->with('base')
                ->get();

            if ($validation->count() > 0) {
                return response()->json($validation);
            }
        }

        $result = Vehicle::create($data);

        $result->vehicleStatusHistory()->create([
            'vehicle_status_id' => VehicleStatusEnum::ACTIVE,
            'user_id' => auth()->user()->id,
        ]);

        return response()->json($result);
    }

    /**
     * POST api/vehicles/force
     *
     * Realiza o cadastro de uma viatura.
     */
    public function forceStore(StoreVehicleRequest $request): JsonResponse
    {
        $data = $request->validated();

        $vehicles = $this->check($request->base_id);

        $result = Vehicle::create($data);

        $result->vehicleStatusHistory()->create([
            'vehicle_status_id' => VehicleStatusEnum::ACTIVE,
            'user_id' => auth()->user()->id,
        ]);

        $vehiclesWithoutChanging = $vehicles->where('latestVehicleStatusHistory.vehicle_status_id', VehicleStatusEnum::COMMITTED->value);

        if ($vehiclesWithoutChanging->count() > 0) {
            return response()->json($vehiclesWithoutChanging);
        }

        return response()->json($result->fresh());
    }

    /**
     * PUT api/vehicles/{id}
     *
     * Atualiza os dados de uma viatura.
     *
     * @urlParam id string required ID da Viatura.
     */
    public function update(UpdateVehicleRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        $result = Vehicle::findOrFail($id);

        if ($data['base_id']) {
            $vehicles = (new VehicleService())->baseHasVehicles($data['base_id'], $id);

            $newVehicleType = Base::select('id', 'vehicle_type_id')->withoutGlobalScope(UrcScope::class)->findOrFail($data['base_id'])->vehicle_type_id;
            $data['vehicle_type_id'] = $newVehicleType;

            if ($vehicles->count() > 0) {
                return response()->json($vehicles);
            }
        }

        $resultArray = $result->toArray();
        unset($resultArray['vehicle_type']);

        if (auth()->user()->hasAnyRole(['radio-operator'])) {
            $diff = Arr::except($data, array_keys($resultArray));
            unset($diff['base_id']);

            if (count($diff) > 0) {
                throw ValidationException::withMessages([
                    'base_id' => 'Você só pode alterar a base vinculada.',
                ]);
            }
        }

        $result->update($data);

        return response()->json($result->fresh());
    }

    /**
     * PUT api/vehicles/force/{id}
     *
     * Força uma atualização de dados de uma viatura, quando houver outra com a mesma base.
     *
     * @urlParam id string required ID da Viatura.
     */
    public function forceUpdate(UpdateVehicleRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        $result = Vehicle::findOrFail($id);

        $vehicles = $this->check($request->base_id);

        $resultArray = $result->toArray();
        unset($resultArray['vehicle_type']);

        if (auth()->user()->hasAnyRole(['radio-operator'])) {
            $diff = Arr::except($data, array_keys($resultArray));
            unset($diff['base_id']);

            if (count($diff) > 0) {
                throw ValidationException::withMessages([
                    'base_id' => 'Você só pode alterar a base vinculada.',
                ]);
            }
        }

        $newVehicleType = Base::select('id', 'vehicle_type_id')->withoutGlobalScope(UrcScope::class)->findOrFail($data['base_id'])->vehicle_type_id;

        $result->update([
            'base_id' => $request->base_id,
            'vehicle_type_id' => $newVehicleType,
        ]);

        $vehiclesWithoutChanging = $vehicles->where('latestVehicleStatusHistory.vehicle_status_id', VehicleStatusEnum::COMMITTED->value);

        if ($vehiclesWithoutChanging->count() > 0) {
            return response()->json($vehiclesWithoutChanging);
        }

        return response()->json($result->fresh());
    }

    private function check(string $id = null): Collection
    {
        $vehicles = Vehicle::where('base_id', $id)
            ->with(
                'latestVehicleStatusHistory:id,vehicle_status_id,vehicle_id,description',
                'base'
            )
            ->get();

        $vehicles->each(function ($vehicle) {
            if ($vehicle->latestVehicleStatusHistory->vehicle_status_id !== VehicleStatusEnum::COMMITTED->value) {
                $vehicle->update(
                    [
                        'base_id' => null,
                    ]
                );
            }
        });

        return $vehicles;
    }
}
