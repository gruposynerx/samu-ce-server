<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\StoreUpdateCoordinationNoteRequest;
use App\Http\Resources\CoordinationNoteResource;
use App\Models\CoordinationNote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Knuckles\Scribe\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group(name: 'Recados da Coordenação', description: 'Gestão de recados da coordenação')]
class CoordinationNoteController extends Controller
{
    /**
     * GET api/coordination-notes
     *
     * Retorna uma lista paginada de Recados da coordenação.
     */
    public function index(SearchRequest $request): ResourceCollection
    {
        $data = $request->validated();

        $results = CoordinationNote::when($request->has('search'), static function (Builder $query) use ($data) {
            $query->whereRaw('note ilike unaccent(?)', "%{$data['search']}%");
        })->paginate(10);

        return CoordinationNoteResource::collection($results);
    }

    /**
     * POST api/coordination-notes
     *
     * Cria um novo Recado da coordenação.
     */
    public function store(StoreUpdateCoordinationNoteRequest $request): JsonResponse
    {
        $data = $request->validated();

        $coordinationNote = CoordinationNote::create($data);

        return response()->json(new CoordinationNoteResource($coordinationNote));
    }

    /**
     * GET api/coordination-notes/{id}
     *
     * Retorna um Recado da coordenação específico.
     *
     * @urlParam id string required ID do Recado da coordenação
     */
    public function show(string $id): JsonResponse
    {
        $result = CoordinationNote::findOrFail($id);

        return response()->json(new CoordinationNoteResource($result));
    }

    /**
     * PUT api/coordination-notes/{id}
     *
     * Atualiza um Recado da coordenação específico.
     *
     * @urlParam id string required ID do Recado da coordenação
     */
    public function update(StoreUpdateCoordinationNoteRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        $result = CoordinationNote::findOrFail($id);

        $result->update($data);

        return response()->json(new CoordinationNoteResource($result->fresh()));
    }

    /**
     * DELETE api/coordination-notes/{id}
     *
     * Deleta um Recado da coordenação específico.
     *
     * @urlParam id string required ID do Recado da coordenação
     */
    public function destroy(string $id): JsonResponse
    {
        $result = CoordinationNote::findOrFail($id);

        $result->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
