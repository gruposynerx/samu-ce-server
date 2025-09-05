<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSceneRecordingCounterreferralRequest;
use App\Http\Resources\SceneRecordingDestinationUnitHistoryResource;
use App\Models\SceneRecordingDestinationUnitHistory;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;
use Symfony\Component\HttpFoundation\Response;

#[Group(name: 'Registro de Cena', description: 'Seção responsável pela gestão do Registro de Cena')]
#[Subgroup(name: 'Contra-referência', description: 'Seção responsável por realizar a contra-referência do Registro de Cena')]
class SceneRecordingCounterreferralController extends Controller
{
    /**
     * POST api/scene-recording/counter-referral
     *
     * Realiza a contra-referência de um registro de cena.
     */
    public function store(StoreSceneRecordingCounterreferralRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = SceneRecordingDestinationUnitHistory::create($data);

        return response()->json(new SceneRecordingDestinationUnitHistoryResource($result), Response::HTTP_CREATED);
    }
}
