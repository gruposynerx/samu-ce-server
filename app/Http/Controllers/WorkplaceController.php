<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Resources\WorkplaceResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use Knuckles\Scribe\Attributes\Group;

#[Group('Locais de trabalho', 'Gerenciamento de locais de trabalho')]
class WorkplaceController extends Controller
{
    /**
     * GET api/workplace
     *
     * Retorna uma lista páginada de todos os locais de trabalho do SAMU.
     */
    public function index(SearchRequest $request): ResourceCollection
    {
        $search = $request->validated('search');

        $urgencyRegulationCenters = DB::table('urgency_regulation_centers')
            ->select('id', 'name');

        $results = DB::table('bases')
            ->whereRaw('unaccent(name) ilike unaccent(?)', "%$search%")
            ->select('id', 'name')
            ->union($urgencyRegulationCenters)
            ->paginate(15);

        return WorkplaceResource::collection($results);
    }
}
