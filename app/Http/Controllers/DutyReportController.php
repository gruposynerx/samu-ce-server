<?php

namespace App\Http\Controllers;

use App\Enums\DutyReportTypeEnum;
use App\Enums\PeriodTypeEnum;
use App\Http\Requests\IndexDutyReportRequest;
use App\Http\Requests\StoreDutyReportRequest;
use App\Http\Requests\UpdateDutyReportRequest;
use App\Http\Requests\VerifyPreviousDutyReportRequest;
use App\Http\Resources\DutyReportResource;
use App\Models\DutyReport;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;
use Ramsey\Uuid\Uuid;

#[Group(name: 'Relatórios', description: 'Seção responsável pela gestão de relatórios')]
#[Subgroup(name: 'Relatório de Plantão', description: 'Seção responsável pela gestão de relatórios de plantão')]
class DutyReportController extends Controller
{
    /**
     * GET api/report/duty/verify
     *
     * Verifica se um relatório de plantão já foi registrado pelo mesmo usuário para a data e turno selecionados.
     *
     * @urlParam id required ID do relatório de plantão.
     */
    public function verifyExistenceOfPreviousReport(VerifyPreviousDutyReportRequest $request): JsonResponse
    {
        return response()->json(['exists' => $this->queryVerifyExistenceOfPreviousReport($request)]);
    }

    /**
     * GET api/report/duty
     *
     * Lista paginada em ordem decrescente com os relatórios de plantão, ordenados por data e turno selecionados.
     *
     * A listagem segue as seguintes regras:
     * - Para usuário com perfil de Chefe de equipe deve exibir apenas os registros dele
     * - Para usuário com perfil de Rádio operador deve exibir apenas os registros dele
     * - Para usuários com perfil de Super admin e admin deve ser exibido todos os registros
     */
    public function index(IndexDutyReportRequest $request): ResourceCollection
    {
        $data = $request->validated();
        $authUser = auth()->user();
        $paginateData = $request->get('all_data') === false;

        $results = DutyReport::with([
            'creator:users.id,users.name',
            'urgencyRegulationCenter:urgency_regulation_centers.id,urgency_regulation_centers.name',
            'medicalRegulators:users.id,users.name',
            'medicalRegulators.urgencyRegulationCenters:urgency_regulation_centers.id,urgency_regulation_centers.name',
            'radioOperators:users.id,users.name',
            'radioOperators.urgencyRegulationCenters:urgency_regulation_centers.id,urgency_regulation_centers.name',
            'tarms:users.id,users.name',
            'tarms.urgencyRegulationCenters:urgency_regulation_centers.id,urgency_regulation_centers.name',
        ])
            ->when($authUser->hasAnyRole(['radio-operator', 'team-leader']), function ($query) use ($authUser) {
                $query->where('created_by', $authUser->id)->where(function ($query) {
                    $query->where(function ($query) {
                        $startOfNocturnalPeriod = now()->subDay()->startOfDay()->hour(7);
                        $endOfNocturnalPeriod = now()->startOfDay()->hour(7);

                        $query->where('period_type_id', PeriodTypeEnum::NOCTURNAL)
                            ->whereBetween('record_at', [$startOfNocturnalPeriod, $endOfNocturnalPeriod]);
                    })->orWhere(function ($query) {
                        $startOfDaytimePeriod = now()->subDay()->startOfDay()->hour(19);
                        $endOfDaytimePeriod = now()->startOfDay()->hour(19);

                        $query->where('period_type_id', PeriodTypeEnum::DAYTIME)
                            ->whereBetween('record_at', [$startOfDaytimePeriod, $endOfDaytimePeriod]);
                    });
                });
            })
            ->when(!empty($data['start_date']) && !empty($data['end_date']), function ($query) use ($data) {
                $query->whereBetween('record_at', [
                    Carbon::create($data['start_date'])->startOfDay(),
                    Carbon::create($data['end_date'])->endOfDay(),
                ]);
            })
            ->when(!empty($data['duty_report_type_id']), function ($query) use ($data) {
                $query->where('duty_report_type_id', $data['duty_report_type_id']);
            })
            ->when($authUser->hasAnyRole(['radio-operator']), function ($query) {
                $query->where('duty_report_type_id', DutyReportTypeEnum::FLEET_MANAGER);
            })
            ->when($authUser->hasAnyRole(['team-leader']), function ($query) {
                $query->where('duty_report_type_id', DutyReportTypeEnum::TEAM_LEADER);
            })
            ->orderByDesc('record_at')
            ->orderBy('period_type_id');

        return DutyReportResource::collection($paginateData ? $results->paginate(20) : $results->get());
    }

    /**
     * GET api/report/duty/{id}
     *
     * Retorna um relatório de plantão específico.
     *
     * @urlParam id required ID do relatório de plantão.
     */
    public function show(int $id): JsonResponse
    {
        $result = DutyReport::with([
            'creator:users.id,users.name',
            'medicalRegulators:users.id,users.name',
            'radioOperators:users.id,users.name',
            'tarms:users.id,users.name',
        ])->findOrFail($id);

        return response()->json(new DutyReportResource($result));
    }

    /**
     * POST api/report/duty
     *
     * Cria um novo relatório de plantão.
     */
    public function store(StoreDutyReportRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($this->queryVerifyExistenceOfPreviousReport($request)) {
            throw ValidationException::withMessages(['message' => 'Já existe um relatório de plantão registrado na data e período selecionado']);
        }

        $periodTime = ((int) $data['period_type_id'] === PeriodTypeEnum::DAYTIME->value) ? 7 : 19;
        $data['record_at'] = Carbon::create($data['record_at'])->startOfDay()->hour($periodTime);
        $result = DutyReport::create($data);

        $professionals = array_merge($request->get('medical_regulators'), $request->get('tarms'), $request->get('radio_operators'));

        $result->professionals()->sync(
            collect($professionals)->map(function ($item) {
                return [
                    'id' => Uuid::uuid4(),
                    'user_id' => $item['user_id'],
                    'current_role_slug' => $item['current_role_slug'],
                ];
            })
        );

        return response()->json(new DutyReportResource($result));
    }

    /**
     * PUT api/report/duty/{id}
     *
     * Edita um relatório de plantão específico
     *
     * @urlParam id required ID do relatório de plantão.
     */
    public function update(UpdateDutyReportRequest $request, string $id): JsonResponse
    {
        $result = DutyReport::findOrFail($id);

        if ($result->created_by !== auth()->user()->id || Carbon::now()->diffInHours($result->record_at) > 24) {
            throw ValidationException::withMessages(['message' => 'Você não tem permissão para editar este relatório.']);
        }

        $result->update($request->validated());

        $professionals = array_merge($request->get('medical_regulators'), $request->get('tarms'), $request->get('radio_operators'));

        $result->professionals()->sync(
            collect($professionals)->map(function ($item) {
                return [
                    'id' => Uuid::uuid4(),
                    'user_id' => $item['user_id'],
                    'current_role_slug' => $item['current_role_slug'],
                ];
            })
        );

        return response()->json(new DutyReportResource($result->fresh()));
    }

    private function queryVerifyExistenceOfPreviousReport(VerifyPreviousDutyReportRequest|StoreDutyReportRequest $data): bool
    {
        $periodTime = ((int) $data['period_type_id'] === PeriodTypeEnum::DAYTIME->value) ? 7 : 19;
        $formattedRecordAt = Carbon::create($data['record_at'])->startOfDay()->hour($periodTime);

        return DutyReport::select('id', 'urc_id', 'record_at', 'period_type_id', 'duty_report_type_id')
            ->where('record_at', $formattedRecordAt)
            ->where('period_type_id', $data['period_type_id'])
            ->where('duty_report_type_id', $data['duty_report_type_id'])
            ->when($data['duty_report_type_id'] === DutyReportTypeEnum::FLEET_MANAGER->value, function ($query) {
                $query->where('created_by', auth()->user()->id);
            })
            ->exists();
    }
}
