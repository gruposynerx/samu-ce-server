<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\StorePowerBIReportRequest;
use App\Http\Requests\UpdatePowerBIReportRequest;
use App\Http\Resources\PowerBIReportResource;
use App\Models\PowerBIReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;
use Symfony\Component\HttpFoundation\Response;

#[Group(name: 'Relatórios', description: 'Seção responsável pela gestão de relatórios')]
#[Subgroup(name: 'Relatórios do Power BI', description: 'Seção responsável pela gestão de relatórios do Power BI')]

class PowerBIReportController extends Controller
{
    /**
     * GET api/power-bi-report
     *
     * Retorna uma lista paginada de relatórios do Power BI.
     */
    public function index(SearchRequest $request): ResourceCollection
    {
        $data = $request->validated();
        $isSuperAdmin = auth()->user()->hasAnyRole(['super-admin']);

        $results = PowerBIReport::with(['urgencyRegulationCenter', 'roles'])
            ->when(!empty($data['search']), function ($query) use ($data) {
                $query->whereRaw('unaccent(name) ilike unaccent(?)', "%{$data['search']}%");
            })
            ->when(!$isSuperAdmin, function ($query) {
                $query->whereHas('roles', function ($query) {
                    $query->where('role_id', auth()->user()->current_role);
                });
            })
            ->orderBy('created_at')
            ->paginate(5);

        return PowerBIReportResource::collection($results);
    }

    /**
     * POST api/power-bi-report
     *
     * Cria um novo Relatório do power BI.
     */
    public function store(StorePowerBIReportRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = PowerBIReport::create($data);

        $result->roles()->sync($data['roles']);

        return response()->json(new PowerBIReportResource($result));
    }

    /**
     * PUT api/power-bi-report/{id}
     *
     * Atualiza um relatório do Power BI específico.
     *
     * @urlParam id string required ID do relatório do Power BI
     */
    public function update(UpdatePowerBIReportRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        $result = PowerBIReport::findOrFail($id);
        $result->update($data);
        $result->roles()->sync($data['roles']);

        return response()->json(new PowerBIReportResource($result->fresh()));
    }

    /**
     * DELETE api/power-bi-report/{id}
     *
     * Deleta um relatório do Power BI específico.
     *
     * @urlParam id string required ID do relatório do Power BI
     */
    public function destroy(string $id): JsonResponse
    {
        $result = PowerBIReport::findOrFail($id);
        $result->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
