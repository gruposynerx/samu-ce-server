<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\StoreRegionalGroupRequest;
use App\Http\Requests\UpdateRegionalGroupRequest;
use App\Http\Resources\RegionalGroupResource;
use App\Models\Base;
use App\Models\RegionalGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use Knuckles\Scribe\Attributes\Group;

#[Group(name: 'Grupos Regionais', description: 'Seção responsável pela gestão de grupos regionais')]
class RegionalGroupController extends Controller
{
    /**
     * GET api/regional-group
     *
     * Retorna uma lista páginada de grupos regionais.
     */
    public function index(SearchRequest $request): ResourceCollection
    {
        $search = $request->validated('search');

        $results = RegionalGroup::with([
            'bases.city',
            'bases.vehicleType',
        ])
            ->when(!empty($search), function ($query) use ($search) {
                $query->whereRaw('unaccent(name) ilike unaccent(?)', "%{$search}%");
            })
            ->orderBy('created_at')
            ->paginate(10);

        return RegionalGroupResource::collection($results);
    }

    /**
     * GET api/regional-group/{id}
     *
     * Retorna um grupo regional específico.
     *
     * @urlParam id string required id do grupo regional
     */
    public function show(string $id): JsonResponse
    {
        $result = RegionalGroup::with('bases')->findOrFail($id);

        return response()->json(new RegionalGroupResource($result));
    }

    /**
     * POST api/regional-group
     *
     * Realiza o cadastro de um grupo regional.
     */
    public function store(StoreRegionalGroupRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = RegionalGroup::create($data);

        Base::whereIn('id', $data['bases'])->update([
            'regional_group_id' => $result->id,
        ]);

        return response()->json(new RegionalGroupResource($result));
    }

    /**
     * PUT api/regional-group/{id}
     *
     * Realiza a atualização de um grupo regional.
     *
     * @urlParam id string required id do grupo regional
     */
    public function update(UpdateRegionalGroupRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        $result = RegionalGroup::findOrFail($id);

        $history = [
            'regional_group_id' => $result->id,
            'previous_regional_group_name' => $result->name,
            'current_regional_group_name' => $data['name'],
            'current_status' => $result['is_active'],
            'previous_linked_bases' => $result->bases->pluck('id'),
            'current_linked_bases' => json_encode($data['bases']),
        ];

        DB::transaction(function () use ($result, $data, $history) {
            $result->update($data);

            Base::where('regional_group_id', $result['id'])
                ->whereNotIn('regional_group_id', $data['bases'])
                ->update(['regional_group_id' => null]);

            Base::whereIn('id', $data['bases'])->update([
                'regional_group_id' => $result->id,
            ]);

            $result->regionalGroupHistory()->create($history);
        });

        return response()->json(new RegionalGroupResource($result->fresh()));
    }

    /**
     * PUT api/regional-group/change-status/{id}
     *
     * Realiza a ativação ou desativação de um grupo regional.
     *
     * @urlParam id string required id do grupo regional
     */
    public function changeStatus(string $id): JsonResponse
    {
        $result = RegionalGroup::findOrFail($id);

        $result->update([
            'is_active' => !$result['is_active'],
        ]);

        return response()->json(new RegionalGroupResource($result->fresh()));
    }
}
