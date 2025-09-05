<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Requests\StoreCyclicScheduleTypeRequest;
use App\Http\Requests\UpdateCyclicScheduleTypeRequest;
use App\Http\Resources\CyclicScheduleTypeResource;
use App\Models\CyclicScheduleType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class CyclicScheduleTypeController extends Controller
{
    /**
     * GET api/cyclic-schedule-type
     *
     * Retorna uma lista páginada de todas as escalas.
     */
    public function index(SearchRequest $request): ResourceCollection
    {
        $search = $request->validated('search');

        $results = CyclicScheduleType::when($search, function ($query) use ($search) {
            $query->where(function ($query) use ($search) {
                $query->whereRaw('unaccent(name) ILIKE unaccent(?)', ["%{$search}%"])
                    ->orWhere('work_hours', 'ILIKE', "%{$search}%")
                    ->orWhere('break_hours', 'ILIKE', "%{$search}%");
            });
        })
            ->orderBy('created_at')
            ->paginate(10);

        return CyclicScheduleTypeResource::collection($results);
    }

    /**
     * POST api/cyclic-schedule-type
     *
     * Realiza o cadastro de uma escala.
     */
    public function store(StoreCyclicScheduleTypeRequest $request): JsonResponse
    {
        $result = CyclicScheduleType::create($request->validated());

        return response()->json(new CyclicScheduleTypeResource($result), Response::HTTP_CREATED);
    }

    /**
     * PUT api/cyclic-schedule-type/{id}
     *
     * Realiza a atualização de uma escala.
     */
    public function update(UpdateCyclicScheduleTypeRequest $request, string $id): JsonResponse
    {
        $data = $request->validated();

        $result = CyclicScheduleType::findOrFail($id);

        $result->update($data);

        return response()->json(new CyclicScheduleTypeResource($result->fresh()));
    }

    /**
     * PUT api/cyclic-schedule-type/change-status/{id}
     *
     * Atualiza o status de uma escala.
     */
    public function changeStatus(string $id): JsonResponse
    {
        $result = CyclicScheduleType::findOrFail($id);

        $result->update(['is_active' => !$result->is_active]);

        return response()->json(new CyclicScheduleTypeResource($result->fresh()));
    }
}
