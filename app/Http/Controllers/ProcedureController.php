<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Resources\ProcedureResource;
use App\Models\Sigtap\Procedure;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group(name: 'Sigtap', description: 'Seção responsável pela gestão de rotas do Sigtap')]
#[Subgroup(name: 'Procedimentos', description: 'Seção responsável pela gestão de Procedimentos')]
class ProcedureController extends Controller
{
    /**
     * GET api/procedures
     *
     * Retorna uma lista páginada de procedimentos.
     */
    public function index(SearchRequest $request): ResourceCollection
    {
        $search = $request->validated('search');

        $results = Procedure::when($search, static function ($query, $search) {
            $query->whereRaw('unaccent(name) ilike unaccent(?)', "%{$search}%")
                ->orWhereRaw('unaccent(code) ilike unaccent(?)', "%{$search}%");
        })->paginate(10);

        return ProcedureResource::collection($results);
    }
}
