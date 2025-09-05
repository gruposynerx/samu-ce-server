<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Resources\MedicineResource;
use App\Models\Medicine;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Knuckles\Scribe\Attributes\Subgroup;

#[Subgroup(name: 'Medicamentos', description: 'Seção responsável pela gestão de Medicamentos')]
class MedicineController extends Controller
{
    /**
     * GET api/medicines
     *
     * Retorna uma lista páginada de medicamentos.
     */
    public function index(SearchRequest $request): ResourceCollection
    {
        $search = $request->validated('search');

        $results = Medicine::when($search, static function ($query, $search) {
            $query->whereRaw('unaccent(name) ilike unaccent(?)', "%{$search}%");
        })->paginate(10);

        return MedicineResource::collection($results);
    }
}
