<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMonitoringSettingRequest;
use App\Http\Resources\MonitoringSettingResource;
use App\Models\MonitoringSetting;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group(name: 'Monitoramento de ocorrências', description: 'Gestão de ocorrências do SAMU')]
#[Subgroup(name: 'Configurações de monitoramento', description: 'Gestão de configurações de monitoramento')]
class MonitoringSettingController extends Controller
{
    /**
     * GET api/attendance-monitoring/settings
     *
     * Retorna as configurações de monitoramento da CRU logada.
     */
    public function show(): JsonResponse
    {
        $result = MonitoringSetting::where('urc_id', auth()->user()->urc_id)->first();

        return response()->json(new MonitoringSettingResource($result));
    }

    /**
     * PUT api/attendance-monitoring/settings
     *
     * Atualiza as configurações de monitoramento da CRU logada.
     */
    public function update(UpdateMonitoringSettingRequest $request): JsonResponse
    {
        $result = MonitoringSetting::where('urc_id', auth()->user()->urc_id)->first();

        $result->update($request->validated());

        return response()->json(new MonitoringSettingResource($result->fresh()));
    }
}
