<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDraftRequest;
use App\Http\Resources\DraftResource;
use App\Models\Draft;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group(name: 'Rascunhos', description: 'Seção responsável pela gestão de rascunhos')]
class DraftController extends Controller
{
    /**
     * GET api/draft
     *
     * Retorna uma lista de todos os rascunhos do usuário logado.
     */
    public function index(): JsonResponse
    {
        $results = Draft::where('created_by', auth()->user()->id)->get();

        return response()->json(DraftResource::collection($results));
    }

    /**
     * GET api/draft/{id}
     *
     * Realiza a busca de um rascunho específico.
     *
     * @urlParam id string required ID do rascunho
     */
    public function show(string $id): JsonResponse
    {
        $result = Draft::findOrFail($id);

        return response()->json(new DraftResource($result));
    }

    /**
     * POST api/draft
     *
     * Realiza o cadastro de um rascunho.
     */
    public function store(StoreDraftRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = Draft::create($data);

        return response()->json(new DraftResource($result));
    }

    /**
     * DELETE api/draft/{id}
     *
     * Deleta um rascunho específico.
     *
     * @urlParam id string required ID do rascunho
     */
    public function destroy(string $id): JsonResponse
    {
        $result = Draft::findOrFail($id);

        $result->delete();

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
