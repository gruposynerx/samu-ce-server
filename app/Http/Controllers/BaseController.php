<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexUnitOrBaseRequest;
use App\Http\Requests\StoreBaseRequest;
use App\Http\Requests\UpdateBaseRequest;
use App\Http\Resources\BaseResource;
use App\Models\Base;
use App\Scopes\UrcScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;
use Symfony\Component\HttpFoundation\Response;

#[Group(name: 'Unidades (Gerais)', description: 'Gestão de unidades (Bases, CRU´S, Unidades Hospitalares)]')]
#[Subgroup(name: 'Bases', description: 'Seção responsável pela gestão de bases do SAMU')]
class BaseController extends Controller
{
    /**
     * GET api/bases
     *
     * Retorna uma lista páginada de todas as bases do SAMU.
     */
    public function index(IndexUnitOrBaseRequest $request): ResourceCollection
    {
        $data = $request->validated();

        $results = Base::with(['city.federalUnit', 'unitType', 'urgencyRegulationCenter', 'vehicleType', 'regionalGroup'])
            ->when(isset($data['is_active']), function ($q) use ($data) {
                $q->where('is_active', $data['is_active']);
            })
            ->withoutGlobalScope(UrcScope::class)
            ->when(isset($data['search']), function ($query) use ($data) {
                $query->whereRaw('unaccent(name) ilike unaccent(?)', "%{$data['search']}%")
                    ->orWhereHas('unitType', static function ($query) use ($data) {
                        $query->whereRaw('unaccent(name) ilike unaccent(?)', "%{$data['search']}%");
                    });
            })
            ->orderBy('created_at')
            ->paginate(10);

        return BaseResource::collection($results);
    }

    /**
     * GET api/bases/{id}
     *
     * Realiza a busca de uma base do samu.
     *
     * @urlParam id string required ID da base
     */
    public function show(string $id): JsonResponse
    {
        $result = Base::withoutGlobalScope(UrcScope::class)->findOrFail($id);

        return response()->json(new BaseResource($result));
    }

     /**
     * GET api/bases/urc/{id}
     *
     *  Retorna uma lista páginada de todas as bases filtradas pelo urc_id do SAMU.
     *
     * @urlParam id string required ID do urc
     */
  public function listByUrc(string $id, IndexUnitOrBaseRequest $request): ResourceCollection
{
    $data = $request->validated();

    $results = Base::with(['city.federalUnit', 'unitType', 'urgencyRegulationCenter', 'vehicleType', 'regionalGroup'])
        ->withoutGlobalScope(UrcScope::class)
        ->where('urc_id', $id)
        ->when(isset($data['is_active']), function ($q) use ($data) {
            $q->where('is_active', $data['is_active']);
        })
        ->when(isset($data['search']), function ($query) use ($data) {
            $query->whereRaw('unaccent(name) ilike unaccent(?)', ["%{$data['search']}%"])
                ->orWhereHas('unitType', function ($subQuery) use ($data) {
                    $subQuery->whereRaw('unaccent(name) ilike unaccent(?)', ["%{$data['search']}%"]);
                });
        })
        ->orderBy('name')
        ->paginate(10);

    return BaseResource::collection($results);
}

     /**
     * POST api/bases
     *
     * Realiza o cadastro de uma base do SAMU.
     */
    public function store(StoreBaseRequest $request): JsonResponse
    {
        $result = Base::create($request->validated());

        return response()->json(new BaseResource($result), Response::HTTP_CREATED);
    }

    /**
     * PUT api/bases/{id}
     *
     * Realiza a atualização de uma base do SAMU.
     *
     * @urlParam id string required id da base
     */
    public function update(UpdateBaseRequest $request, string $id): JsonResponse
    {
        $result = Base::withoutGlobalScope(UrcScope::class)->findOrFail($id);

        $result->update($request->validated());

        return response()->json(new BaseResource($result->fresh()));
    }

    /**
     * PUT api/bases/change-status/{id}
     *
     * Realiza a ativação ou desativação de uma base do SAMU.
     *
     * @urlParam id string required id da base
     */
    public function changeStatus(string $id): JsonResponse
    {
        $result = Base::withoutGlobalScope(UrcScope::class)->findOrFail($id);
        $newStatus = !$result['is_active'];

        if (!$newStatus && $result->vehicles()->count()) {
            throw ValidationException::withMessages(['base' => 'Você possui viaturas vinculadas a base. Desvincule as viaturas para desativá-la.']);
        }

        $result->update(['is_active' => $newStatus]);

        return response()->json(new BaseResource($result->fresh()));
    }

    /**
     * GET api/bases/tracking-bases
     *
     * Retorna uma lista paginada de bases de rastreio.
     */
    public function trackingBases(): ResourceCollection
    {
        $results = Base::withoutGlobalScope(UrcScope::class)
            ->whereHas('vehicles', function ($query) {
                $query->whereNotNull('tracking_device_imei')
                    ->whereNotNull('tracking_system_id');
            })
            ->get();

        return BaseResource::collection($results);
    }
}
