<?php

namespace App\Http\Controllers;

use App\Enums\VehicleStatusEnum;
use App\Http\Requests\StoreVehicleStatusHistoryRequest;
use App\Models\Base;
use App\Models\Vehicle;
use App\Scopes\UrcScope;
use App\Services\VehicleService;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group(name: 'Viaturas', description: 'Seção responsável pela gestão de viaturas')]
#[Subgroup(name: 'Status da Viatura', description: 'Seção responsável pela gestão de status da viatura')]
class VehicleStatusHistoryController extends Controller
{
    /**
     * POST api/vehicles/{id}/status
     *
     * Realiza o registro de um novo status para a viatura e altera a base (caso necessário).
     *
     * @urlParam id string required ID da viatura.
     */
    public function store(StoreVehicleStatusHistoryRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        $vehicle = Vehicle::findOrFail($id);

        if (!empty($data['base_id'])) {
            $vehicles = (new VehicleService())->baseHasVehicles($data['base_id'], $id);

            if ($vehicles->count() > 0) {
                return response()->json($vehicles);
            }
        }

        if (in_array($data['vehicle_status_id'], array_column(VehicleStatusEnum::MANUAL_STATUSES, 'value'))) {
            if ($request->has('base_id')) {
                $base = Base::select('id', 'vehicle_type_id')->withoutGlobalScope(UrcScope::class)->find($data['base_id']);
                $newVehicleType = $base ? $base->vehicle_type_id : $vehicle->vehicle_type_id;

                $vehicle->update([
                    'base_id' => $request->get('base_id'),
                    'vehicle_type_id' => $newVehicleType,
                ]);
            }

            $vehicle->vehicleStatusHistory()->create($data);
        }

        return response()->json($vehicle->fresh());
    }
}
