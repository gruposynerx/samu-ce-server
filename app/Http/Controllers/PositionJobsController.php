<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePositionJobRequest;
use App\Http\Requests\UpdatePositionJobRequest;
use App\Http\Resources\PositionJobResource;
use App\Models\PositionJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PositionJobsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = PositionJob::query();

        if ($request->has('search')) {
            $query->where('name', 'ilike', '%' . $request->get('search') . '%');
        }

        $query->orderBy('name');

        $positionJobs = $query->paginate($request->get('per_page', 15));

        return PositionJobResource::collection($positionJobs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePositionJobRequest $request): JsonResponse
    {
        $positionJob = PositionJob::create($request->validated());

        return response()->json(new PositionJobResource($positionJob));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $positionJob = PositionJob::findOrFail($id);

        return response()->json(new PositionJobResource($positionJob));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePositionJobRequest $request, string $id): JsonResponse
    {
        $positionJob = PositionJob::findOrFail($id);
        $positionJob->update($request->validated());

        return response()->json(new PositionJobResource($positionJob->fresh()));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $positionJob = PositionJob::findOrFail($id);
        $positionJob->delete();

        return response()->json([
            'message' => 'Cargo excluído com sucesso.'
        ]);
    }
}
