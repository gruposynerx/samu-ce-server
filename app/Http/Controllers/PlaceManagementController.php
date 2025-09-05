<?php

namespace App\Http\Controllers;

use App\Enums\PlaceStatusEnum;
use App\Http\Requests\IndexPlaceManagementRequest;
use App\Http\Requests\StorePlaceManagementRequest;
use App\Http\Requests\UpdatePlaceManagementRequest;
use App\Http\Resources\PlaceManagementResource;
use App\Models\PlaceManagement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Group;

#[Group(name: 'Locais', description: 'Gestão de locais)]')]
class PlaceManagementController extends Controller
{
    /**
     * GET api/place
     *
     * Retorna uma lista páginada de todos os locais.
     */
    public function index(IndexPlaceManagementRequest $request): ResourceCollection
    {
        $data = $request->validated();

        $results = PlaceManagement::with('user:users.id,users.name')
            ->when(!empty($data['search']), function ($query) use ($data) {
                $query->where(function ($query) use ($data) {
                    $query->whereRaw('unaccent(name) ilike unaccent(?)', "%{$data['search']}%")
                        ->orWhereHas('user', static function ($query) use ($data) {
                            $query->whereRaw('unaccent(users.name) ilike unaccent(?)', "%{$data['search']}%");
                        })
                        ->orWhereHas('placeStatus', static function ($query) use ($data) {
                            $query->whereRaw('unaccent(place_statuses.name) ilike unaccent(?)', "%{$data['search']}%");
                        });
                });
            })
            ->when(!empty($data['place_statuses']), function ($query) use ($data) {
                $query->whereIn('place_status_id', $data['place_statuses']);
            })
            ->orderByRaw('CASE user_id WHEN ? THEN 0 ELSE 1 END, name', auth()->user()->id)
            ->paginate(10);

        return PlaceManagementResource::collection($results);
    }

    /**
     * POST api/place
     *
     * Realiza o cadastro de um local.
     */
    public function store(StorePlaceManagementRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = PlaceManagement::create(['place_status_id' => PlaceStatusEnum::FREE->value, ...$data]);

        return response()->json(new PlaceManagementResource($result));
    }

    /**
     * PUT api/place/{id}
     *
     * Realiza a atualização de um local.
     */
    public function update(UpdatePlaceManagementRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        $result = PlaceManagement::findOrFail($id);
        $result->update($data);

        return response()->json(new PlaceManagementResource($result->fresh()));
    }

    /**
     * PUT api/place/vacate/{id}
     *
     * Desocupa um local.
     *
     * @urlParam id string required ID do local
     */
    public function vacate(string $id): JsonResponse
    {
        $result = PlaceManagement::findOrFail($id);

        if ($result->user()->doesntExist()) {
            throw ValidationException::withMessages(['user_id' => 'O local não está ocupado.']);
        }

        $result->update(['user_id' => null, 'place_status_id' => PlaceStatusEnum::FREE->value]);

        return response()->json(new PlaceManagementResource($result));
    }

    /**
     * PUT api/place/occupy/{id}
     *
     * Ocupa um local.
     *
     * @urlParam id string required ID do local
     */
    public function occupy(string $id): JsonResponse
    {
        $result = PlaceManagement::findOrFail($id);
        $user = auth()->user();

        if ($user->place) {
            $user->place->update([
                'user_id' => null,
                'place_status_id' => PlaceStatusEnum::FREE->value,
            ]);
        }

        $result->update([
            'user_id' => $user->id,
            'place_status_id' => PlaceStatusEnum::OCCUPIED->value,
        ]);

        return response()->json(new PlaceManagementResource($result));
    }

    /**
     * PUT api/place/activate-or-inactivate/{id}
     *
     * Ativa ou desativa um local.
     *
     * @urlParam id string required ID do local
     */
    public function activateOrInactivate(string $id): JsonResponse
    {
        $result = PlaceManagement::findOrFail($id);

        if ($result->place_status_id === PlaceStatusEnum::OCCUPIED->value) {
            throw ValidationException::withMessages(['place_status_id' => 'O local precisa estar livre para ter seu status alterado.']);
        }

        $newStatus = $result->place_status_id === PlaceStatusEnum::DISABLED->value ? PlaceStatusEnum::FREE->value : PlaceStatusEnum::DISABLED->value;
        $result->update(['place_status_id' => $newStatus]);

        return response()->json(new PlaceManagementResource($result));
    }
}
