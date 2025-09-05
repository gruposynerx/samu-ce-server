<?php

namespace App\Http\Controllers;

use App\Http\Resources\UrgencyRegulationCenterResource;
use App\Models\UrgencyRegulationCenter;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group(name: 'Unidades (Gerais)', description: 'Gestão de unidades (Bases, CRU´S, Unidades Hospitalares)]')]
#[Subgroup(name: 'Central de Regulação de Urgência', description: 'Seção responsável pela gestão das CRU´S')]
class UrgencyRegulationCenterController extends Controller
{
    /**
     * GET api/urgency-regulation-centers
     *
     * Retorna uma lista com todas as centrais de regulação de urgência.
     */
    public function index(): JsonResponse
    {
        $results = UrgencyRegulationCenter::all();

        return response()->json(UrgencyRegulationCenterResource::collection($results));
    }
}
