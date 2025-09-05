<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreShiftRequest;
use App\Http\Requests\UpdateShiftRequest;
use App\Http\Resources\ShiftResource;
use App\Models\Shift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ShiftController extends Controller
{
    /**
     * GET api/turns
     * Lista todos os turnos.
     */
    public function index(): ResourceCollection
    {
        $turns = Shift::paginate(10);

        return ShiftResource::collection($turns);
    }

    /**
     * GET api/turns/{id}
     * Retorna um turno específico.
     *
     * @urlParam id int required ID do turno
     */
    public function show(int $id): JsonResponse
    {
        $turn = Shift::findOrFail($id);

        return response()->json(new ShiftResource($turn));
    }

    /**
     * POST api/turns
     * Cria um novo turno.
     */
    public function store(StoreShiftRequest $request): JsonResponse
    {
        $turn = Shift::create($request->validated());
        return response()->json(new ShiftResource($turn), 201);
    }

    /**
     * PUT api/turns/{id}
     * Atualiza um turno existente.
     *
     * @urlParam id int required ID do turno
     */
    public function update(UpdateShiftRequest $request, int $id): JsonResponse
    {
        $turn = Shift::findOrFail($id);
        $turn->update($request->validated());

        return response()->json(new ShiftResource($turn->fresh()));
    }

    /**
     * DELETE api/turns/{id}
     * Deleta um turno.
     *
     * @urlParam id int required ID do turno
     */
    public function destroy(int $id): JsonResponse
    {
        $turn = Shift::findOrFail($id);
        $turn->delete();

        return response()->json(['message' => 'Turno deletado com sucesso.']);
    }
}
