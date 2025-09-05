<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateBPARequest;
use App\Jobs\GenerateBPAJob;
use App\Models\BPAReport;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group(name: 'Relatórios', description: 'Seção responsável pela gestão de relatórios')]
#[Subgroup(name: 'BPA', description: 'Seção responsável pela gestão do BPA')]
class BPAController extends Controller
{
    /**
     * PUT api/reports/bpa
     *
     * Inicia a geração do relatório BPA para determinado período.
     */
    public function generate(GenerateBPARequest $request): JsonResponse
    {
        $period = [
            'start' => Carbon::parse($request->post('start_date'))->startOfDay(),
            'end' => Carbon::parse($request->post('end_date'))->endOfDay(),
        ];

        $urcId = $request->post('urc_id');

        $key = $urcId . $period['start']->format('Ymd') . $period['end']->format('Ymd');

        $report = BPAReport::where('key', $key)->firstOrCreate(['key' => $key]);

        if ($report->wasRecentlyCreated) {
            GenerateBPAJob::dispatchAfterResponse($period, $urcId);
        }

        return response()->json($report);
    }
}
