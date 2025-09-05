<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexUnitOrBaseRequest;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Http\Resources\UnitResource;
use App\Models\City;
use App\Models\FederalUnit;
use App\Models\Unit;
use App\Models\UnitType;
use App\Services\CnesService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Str;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group(name: 'Unidades (Gerais)', description: 'Gestão de unidades (Bases, CRU´S, Unidades Hospitalares)]')]
#[Subgroup(name: 'Unidades', description: 'Seção responsável pela gestão de unidades hospitalares')]
class UnitController extends Controller
{
    /**
     * GET api/units
     *
     * Retorna uma lista páginada de todas as unidades de saúde.
     */
    public function index(IndexUnitOrBaseRequest $request): ResourceCollection
    {
        $data = $request->validated();

        $results = Unit::with(['city.federalUnit', 'unitType'])
            ->when(isset($data['search']), function ($query) use ($data) {
                $query->where(function (Builder $query) use ($data) {
                    $query->whereRaw('unaccent(name) ilike unaccent(?)', "%{$data['search']}%")
                        ->orWhereHas('unitType', static function ($query) use ($data) {
                            $query->whereRaw('unaccent(name) ilike unaccent(?)', "%{$data['search']}%");
                        })
                        ->orWhereHas('city', fn ($query) => $query->whereRaw('unaccent(name) ilike unaccent(?)', "%{$data['search']}%"));
                });
            })
            ->when(isset($data['is_active']), function ($q) use ($data) {
                $q->where('is_active', $data['is_active']);
            })
            ->paginate(10);

        return UnitResource::collection($results);
    }

    /**
     * GET api/units/{id}
     *
     * Realiza a busca de uma unidade de saúde.
     *
     * @urlParam id string required ID da unidade
     */
    public function show(string $id): JsonResponse
    {
        $result = Unit::with(['city.federalUnit', 'unitType'])->findOrFail($id);

        return response()->json(new UnitResource($result));
    }

    /**
     * POST api/units
     *
     * Realiza o cadastro de uma unidade de saúde.
     */
    public function store(StoreUnitRequest $request): JsonResponse
    {
        $result = Unit::create($request->validated());

        return response()->json(new UnitResource($result));
    }

    /**
     * PUT api/units/{id}
     *
     * Realiza a atualização de uma unidade de saúde.
     *
     * @urlParam id string required ID da unidade
     */
    public function update(UpdateUnitRequest $request, string $id): JsonResponse
    {
        $result = Unit::find($id);
        $result->update($request->validated());

        return response()->json(new UnitResource($result->fresh()));
    }

    /**
     * PUT api/units/{id}/change-status
     *
     * Realiza a ativação ou desativação de uma unidade de saúde.
     *
     * @urlParam id string required ID da unidade
     */
    public function changeStatus(string $id): JsonResponse
    {
        $result = Unit::findOrFail($id);
        $result->update(['is_active' => !$result->is_active]);

        return response()->json(new UnitResource($result->fresh()));
    }

    /**
     * GET api/units/fetch-by-registration/{registration}
     *
     * Busca uma unidade de saúde pelo CNES.
     *
     * @urlParam registration string required CNES da unidade
     */
    public function fetchByRegistration(string $registration): JsonResponse
    {

        $result = app(CnesService::class)->fetchByRegistration($registration);

        if ($result->federalUnitCode) {
            $result->federalUnit = FederalUnit::where('ibge_code', $result->federalUnitCode)->first();
        }

        if ($result->cityCode) {
            $result->city = City::where('ibge_code', 'ilike', "$result->cityCode%")->first();
        }

        if ($result->unitTypeId) {
            $result->unitType = UnitType::where('id', $result->unitTypeId)->first();
        }

        $converted = [];
        foreach ($result as $key => $value) {
            $converted[Str::snake($key)] = $value;
        }

        return response()->json($converted);
    }
}
