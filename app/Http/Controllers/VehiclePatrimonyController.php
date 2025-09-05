<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateVehiclePatrimoniesRequest;
use App\Models\Patrimony;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group(name: 'Viaturas', description: 'Seção responsável pela gestão de viaturas')]
#[Subgroup(name: 'Equipamentos da Viatura', description: 'Seção responsável pela gestão de equipamentos da viatura')]
class VehiclePatrimonyController extends Controller
{
    /**
     * GET api/vehicles/{id}/patrimonies
     *
     * Retorna os equipamentos vinculados a uma determinada viatura.
     *
     * @urlParam id string required ID da viatura.
     */
    public function index(string $id): JsonResponse
    {
        $results = Patrimony::where('vehicle_id', $id)->get();

        return response()->json($results);
    }

    /**
     * PUT api/vehicles/{id}/patrimonies
     *
     * Vincula equipamentos ao veiculo.
     *
     * @urlParam id string required ID da viatura.
     */
    public function update(UpdateVehiclePatrimoniesRequest $request, string $id): Response
    {
        $patrimonies = $request->validated('patrimonies');

        Patrimony::where('vehicle_id', $id)->update(['vehicle_id' => null]);
        Patrimony::whereIn('id', $patrimonies)->update(['vehicle_id' => $id]);

        return response()->noContent();
    }

    /**
     * DELETE api/vehicles/{id}/patrimonies/{patrimonyId}
     *
     * Remove um equipamento de uma determinada viatura.
     *
     * @urlParam id string required ID da viatura.
     * @urlParam patrimonyId string required ID do equipamento.
     */
    public function destroy(string $id, string $patrimonyId): JsonResponse
    {
        $result = Patrimony::where('vehicle_id', $id)->findOrFail($patrimonyId);
        $result->update(['vehicle_id' => null]);

        return response()->json($result);
    }
}
