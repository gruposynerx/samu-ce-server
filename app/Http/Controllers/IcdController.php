<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Resources\IcdResource;
use App\Models\Sigtap\Icd;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group(name: 'Sigtap', description: 'Seção responsável pela gestão de rotas do Sigtap')]
#[Subgroup(name: 'CID', description: 'Seção responsável pela gestão de CIDS')]
class IcdController extends Controller
{
    /**
     * GET api/icds
     *
     * Retorna uma lista páginada de CIDS.
     */
    public function index(SearchRequest $request): ResourceCollection
    {
        $search = $request->validated('search');

        $results = Icd::when($search, static function ($query, $search) {
            $query->whereRaw('unaccent(description) ilike unaccent(?)', "%{$search}%")
                ->orWhereRaw('unaccent(code) ilike unaccent(?)', "%{$search}%");
        })->paginate(10);

        return IcdResource::collection($results);
    }
}
