<?php

namespace App\Http\Controllers;

use App\Http\Resources\FederalUnitResource;
use App\Models\FederalUnit;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group(name: 'Cidades/UF', description: 'Gestão de Cidades e Unidades Federativas')]
#[Subgroup(name: 'Unidade Federativa', description: 'Gestão de unidades federativas')]
class FederalUnitController extends Controller
{
    /**
     * GET api/federal-units/
     *
     * Retorna uma lista de todas as unidades federativas (UF), em ordem alfabética.
     */
    public function index(): JsonResponse
    {
        $results = FederalUnit::orderBy('name')->get();

        return response()->json(FederalUnitResource::collection($results));
    }
}
