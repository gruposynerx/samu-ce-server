<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetAbleProfessionalsRequest;
use App\Http\Resources\RadioOperationFleetResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleOccupation;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Validation\ValidationException;

class RadioOperationFleetController extends Controller
{
    /**
     * GET api/radio-operation/fleet/able-occupations/{vehicleId}
     *
     * Retorna todos os CBOS que podem ser utilizados para o veículo informado.
     *
     * @urlParam vehicleType required Tipo de veículo. Example: 1
     */
    public function getAbleOccupations(string $vehicleId): ResourceCollection
    {
        $vehicle = Vehicle::findOrFail($vehicleId);

        if ($vehicle->base()->doesntExist()) {
            throw ValidationException::withMessages(['vehicle_id' => 'Veículo não possui base cadastrada.']);
        }

        $ableOccupations = VehicleOccupation::with('occupation')->where('vehicle_type_id', $vehicle->base->vehicle_type_id)->get();

        return RadioOperationFleetResource::collection($ableOccupations);
    }

    /**
     * GET api/radio-operation/fleet/able-occupations/{vehicleId}
     *
     * Retorna todos os CBOS que podem ser utilizados para o tipo de veículo informado.
     *
     * @urlParam vehicleType required Tipo de veículo. Example: 1
     */
    public function getAbleOccupationsByType(int $vehicleType): ResourceCollection
    {
        $ableOccupations = VehicleOccupation::with('occupation')->where('vehicle_type_id', $vehicleType)->get();

        return RadioOperationFleetResource::collection($ableOccupations);
    }

    /**
     * GET api/radio-operation/fleet/able-professionals
     *
     * Retorna todos os usuários que contenham algum dos CBOS informados.
     */
    public function getAbleProfessionals(GetAbleProfessionalsRequest $request): ResourceCollection
    {
        $occupations = $request->validated('occupations');
        $search = $request->validated('search');

        $ableProfessionals = User::whereIn('cbo', $occupations)->orWhere(function ($query) use ($occupations) {
            if (in_array('2251', $occupations)) {
                $query->where('cbo', 'ilike', '2251%');
            }
        })->when($search ?? null, function ($query) use ($search) {
            $query->whereRaw('unaccent(name) ilike unaccent(?)', "%{$search}%")->orWhereRaw('unaccent(identifier) ilike unaccent(?)', "%{$search}%");
        })->paginate(10);

        return UserResource::collection($ableProfessionals);
    }
}
