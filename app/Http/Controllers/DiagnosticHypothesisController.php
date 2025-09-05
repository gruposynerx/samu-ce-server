<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiagnosticHypothesisRequest;
use App\Http\Requests\IndexDiagnosticHypothesisRequest;
use App\Http\Resources\DiagnosticHypothesisResource;
use App\Models\DiagnosticHypothesis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Knuckles\Scribe\Attributes\Group;

#[Group(name: 'Hipótese Diagnóstica', description: 'Seção responsável pela gestão de hipóteses diagnósticas')]
class DiagnosticHypothesisController extends Controller
{
    /**
     * GET api/diagnostic-hypothesis
     *
     * Retorna uma lista de hipóteses diagnósticas.
     */
    public function index(IndexDiagnosticHypothesisRequest $request): ResourceCollection
    {
        $data = $request->validated();

        $results = DiagnosticHypothesis::when(!empty($data['filter_by_active']), static function ($query) {
            $query->where('is_active', true);
        })
            ->when(!empty($data['load_nature_types']), static function ($query) {
                $query->with('natureTypes');
            })
            ->when(!empty($data['search']), static function ($query) use ($data) {
                $query->whereRaw('unaccent(diagnostic_hypotheses.name) ilike unaccent(?)', "%{$data['search']}%")->when(!empty($data['load_nature_types']), static function ($query) use ($data) {
                    $query->orWhereHas('natureTypes', fn ($q) => $q->whereRaw('unaccent(nature_types.name) ilike unaccent(?)', "%{$data['search']}%"));
                });
            })
            ->when(!empty($data['nature_types']), static function ($query) use ($data) {
                $query->whereHas('natureTypes', fn ($q) => $q->whereIn('nature_type_id', $data['nature_types']));
            })
            ->orderBy('id')
            ->paginate($data['per_page'] ?? 10);

        return DiagnosticHypothesisResource::collection($results);
    }

    /**
     * POST api/diagnostic-hypothesis
     *
     * Cadastra uma nova hipótese diagnóstica.
     */
    public function store(DiagnosticHypothesisRequest $request): JsonResponse
    {
        $result = DiagnosticHypothesis::create($request->except('nature_types_id'));

        $result->natureTypes()->attach($request->validated('nature_types_id'));

        return response()->json(new DiagnosticHypothesisResource($result));
    }

    /**
     * PUT api/diagnostic-hypothesis/{id}
     *
     * Atualiza uma hipótese diagnóstica.
     */
    public function update(DiagnosticHypothesisRequest $request, int $id): JsonResponse
    {
        $result = DiagnosticHypothesis::findOrFail($id);

        $result->update($request->except('nature_types_id'));

        $result->natureTypes()->sync($request->validated('nature_types_id'));

        return response()->json(new DiagnosticHypothesisResource($result->fresh()));
    }

    /**
     * PUT api/diagnostic-hypothesis/change-status/{id}
     *
     * Atualiza o status de uma hipótese diagnóstica.
     */
    public function changeStatus(int $id): JsonResponse
    {
        $result = DiagnosticHypothesis::findOrFail($id);

        $result->update(['is_active' => !$result->is_active]);

        return response()->json(new DiagnosticHypothesisResource($result->fresh()));
    }
}
