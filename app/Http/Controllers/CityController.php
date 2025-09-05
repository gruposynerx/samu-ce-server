<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexCityRequest;
use App\Http\Resources\CityResource;
use App\Models\City;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group(name: 'Cidades/UF', description: 'Gestão de Cidades e Unidades Federativas')]
#[Subgroup(name: 'Cidades', description: 'Gestão de cidades')]
class CityController extends Controller
{
    /**
     * GET api/cities
     *
     * Retorna uma lista páginada de cidades, sendo possível pesquisar por nome ou código do ibge (podendo filtrar por UF).
     */
    public function index(IndexCityRequest $request)
    {
        $federalUnitId = $request->validated('federal_unit_id');
        $search = $request->validated('search');

        $results = City::where(function ($q) use ($request, $search) {
            $q->when($request->has('search'), function ($query) use ($search) {
                $query->whereRaw('unaccent(ibge_code) ilike unaccent(?)', "%{$search}%")
                    ->orWhereRaw('unaccent(name) ilike unaccent(?)', "%{$search}%");
            });
        })
            ->when($request->has('federal_unit_id'), fn ($query) => $query->where('federal_unit_id', $federalUnitId))
            ->orderBy('name')
            ->paginate();

        return CityResource::collection($results);
    }
}
