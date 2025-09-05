<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Resources\BaseResource;
use App\Models\Base;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Knuckles\Scribe\Attributes\Group;

#[Group('Veículos das Bases', 'Gerenciamento de Veiculos das Bases')]
class BaseVehiclesController extends Controller
{
    public function index(SearchRequest $request): ResourceCollection
    {
        $results = Base::select([
            'id',
            'name',
        ])
            ->with([
                'vehicles:id,code,license_plate,vehicle_type_id,base_id',
                'vehicles.latestVehicleStatusHistory:id,vehicle_status_id,vehicle_id',
                'city',
            ])
            ->when(!empty($request->search), fn (Builder $q) => $q->whereRaw('unaccent(name) ilike unaccent(?)', ["%{$request->search}%"]))
            ->orderBy('name')
            ->paginate(15);

        return BaseResource::collection($results);
    }
}
