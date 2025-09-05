<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Resources\CityResource;
use App\Models\City;
use App\Scopes\UrcScope;
use Illuminate\Database\Eloquent\Builder;
use Knuckles\Scribe\Attributes\Group;

#[Group('Veículos das Cidades', 'Gerenciamento de Veiculos das Cidades')]
class CityVehiclesController extends Controller
{
    public function index(SearchRequest $request)
    {
        $data = City::with([
            'vehicles' => fn ($q) => $q->availableForCurrentUrc(),
            'vehicles.latestVehicleStatusHistory:id,vehicle_status_id,vehicle_id,description,attendance_id',
            'vehicles.latestVehicleStatusHistory.attendance:id,ticket_id,attendance_sequence_per_ticket',
            'vehicles.latestVehicleStatusHistory.attendance.ticket:id,ticket_sequence_per_urgency_regulation_center',
            'vehicles.base' => fn ($q) => $q->withoutGlobalScope(UrcScope::class),
        ])
            ->whereHas('vehicles', fn (Builder $q) => $q->availableForCurrentUrc())
            ->when(!empty($request->search), function ($q) use ($request) {
                $q->whereRaw('unaccent(name) ilike unaccent(?)', ["%{$request->search}%"])
                    ->orWhereHas('vehicles', function ($q) use ($request) {
                        $q->availableForCurrentUrc()
                            ->where(function ($q) use ($request) {
                                $q->whereRaw('unaccent(license_plate) ilike unaccent(?)', ["%{$request->search}%"])
                                    ->orWhereRaw('unaccent(code) ilike unaccent(?)', ["%{$request->search}%"])
                                    ->orWhereHas('vehicleType', function ($q) use ($request) {
                                        $q->whereRaw('unaccent(vehicle_types.name) ilike unaccent(?)', ["%{$request->search}%"]);
                                    });
                            });
                    });
            })
            ->orderBy('name')
            ->paginate(15);

        return CityResource::collection($data);
    }
}
