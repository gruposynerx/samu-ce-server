<?php

namespace App\Http\Controllers;

use App\Enums\PatrimonyStatusEnum;
use App\Http\Requests\PatrimonySearchRequest;
use App\Http\Requests\StorePatrimonyRequest;
use App\Http\Requests\UpdatePatrimonyRequest;
use App\Http\Resources\PatrimonyResource;
use App\Models\Patrimony;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Group;

#[Group(name: 'Patrimônios', description: 'Gestão de patrimônios')]
class PatrimonyController extends Controller
{
    /**
     * GET api/patrimonies
     *
     * Retorna uma lista paginada de patrimônios.
     */
    public function index(PatrimonySearchRequest $request): JsonResource
    {
        $search = $request->validated('search');
        $data = $request->validated();

        $results = Patrimony::with('vehicle', 'patrimonyType')
            ->when(isset($search), function (Builder $query) use ($search) {
                $query
                    ->whereHas('vehicle', function (Builder $query) use ($search) {
                        $query->whereRaw('unaccent(chassis) ilike unaccent(?)', "%{$search}%");
                    })
                    ->orWhereHas('patrimonyType', function (Builder $query) use ($search) {
                        $query->whereRaw('unaccent(name) ilike unaccent(?)', "%{$search}%");
                    })
                    ->orWhereRaw('unaccent(identifier) ilike unaccent(?)', "%{$search}%");
            })
            ->when(isset($data['only_available']) && $data['only_available'], function (Builder $query) {
                $query->whereDoesntHave('vehicle')
                    ->where('patrimony_status_id', PatrimonyStatusEnum::AVAILABLE);
            })
            ->when($request->has('vehicles'), fn ($query) => $query->whereIn('vehicle_id', $request->input('vehicles')))
            ->paginate(10);

        return PatrimonyResource::collection($results);
    }

    /**
     * GET api/patrimonies/{id}
     *
     * Retorna um patrimônio específico.
     *
     * @urlParam id string required ID do patrimônio
     */
    public function show(string $id): JsonResponse
    {
        $result = Patrimony::with('vehicle')->findOrFail($id);

        return response()->json(new PatrimonyResource($result));
    }

    /**
     * POST api/patrimonies
     *
     * Realiza o cadastro de um patrimônio.
     */
    public function store(StorePatrimonyRequest $request): JsonResponse
    {
        $data = $request->validated();

        $status = PatrimonyStatusEnum::tryFrom($data['patrimony_status_id']);

        if ($status === PatrimonyStatusEnum::RETAINED) {
            throw ValidationException::withMessages(['patrimony_status_id' => 'Não é possível criar um equipamento retido.']);
        }

        if ($status === PatrimonyStatusEnum::UNAVAILABLE && isset($data['vehicle_id'])) {
            throw ValidationException::withMessages(['patrimony_status_id' => 'Não é possível criar um equipamento indisponível com uma VTR vinculada.']);
        }

        $result = Patrimony::create($data);

        return response()->json(new PatrimonyResource($result));
    }

    /**
     * PUT api/patrimonies/{id}
     *
     * Atualiza os dados de um patrimônio.
     *
     * @urlParam id string required ID do patrimônio
     */
    public function update(UpdatePatrimonyRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        $result = Patrimony::findOrFail($id);

        $newStatus = PatrimonyStatusEnum::tryFrom($data['patrimony_status_id']);
        $patrimonyIsRetained = PatrimonyStatusEnum::tryFrom($result->patrimony_status_id) === PatrimonyStatusEnum::RETAINED;

        if ($patrimonyIsRetained && $newStatus !== PatrimonyStatusEnum::RETAINED) {
            throw ValidationException::withMessages(['patrimony_status_id' => 'Não é possível alterar o status de um equipamento retido.']);
        }

        if ($newStatus === PatrimonyStatusEnum::RETAINED && !$patrimonyIsRetained) {
            throw ValidationException::withMessages(['patrimony_status_id' => 'Não é possível reter um equipamento por este formulário.']);
        }

        if ($patrimonyIsRetained && (int) $data['vehicle_id'] !== $result->vehicle_id) {
            throw ValidationException::withMessages(['vehicle_id' => 'Não é possível alterar a VTR de um equipamento retido.']);
        }

        if ($newStatus === PatrimonyStatusEnum::UNAVAILABLE && isset($data['vehicle_id'])) {
            throw ValidationException::withMessages(['patrimony_status_id' => 'Esse equipamento possui uma VTR vinculada, para deixá-lo indisponível, desvincule a VTR.']);
        }

        $result->update($data);

        return response()->json(new PatrimonyResource($result->fresh()));
    }
}
