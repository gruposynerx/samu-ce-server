<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatusEnum;
use App\Enums\PatrimonyStatusEnum;
use App\Enums\TicketTypeEnum;
use App\Enums\VehicleStatusEnum;
use App\Http\Requests\AttendanceBaseStatusesRequest;
use App\Http\Requests\DashboardRequest;
use App\Http\Requests\FleetRequest;
use App\Http\Requests\OcurrenceTypesByAttendanceStatusRequest;
use App\Http\Resources\AttendancesPerProfessionalResource;
use App\Models\Attendance;
use App\Models\PrimaryAttendance;
use App\Models\SecondaryAttendance;
use App\Scopes\AttendanceScope;
use App\Scopes\UrcScope;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Knuckles\Scribe\Attributes\Group;

#[Group(name: 'Dashboard', description: 'Gerenciamento da dashboard.')]
class DashboardController extends Controller
{
    private int $periodIntervalInHours = 12;

    protected Carbon $endOfCurrentPeriod;

    protected Carbon $startOfCurrentPeriod;

    protected Carbon $endOfLastPeriod;

    protected Carbon $startOfLastPeriod;

    protected string $primaryAttendanceSlug;

    protected string $secondaryAttendanceSlug;

    /**
     * @throws \Exception
     */
    private function formatTimeDuration(?string $seconds): string
    {
        if (empty($seconds)) {
            return 'Nenhum registro';
        }

        return CarbonInterval::seconds($seconds)->cascade()->forHumans(['short' => true]);
    }

    public function __construct()
    {
        $this->endOfCurrentPeriod = now();
        $this->startOfCurrentPeriod = $this->endOfCurrentPeriod->copy()->subHours($this->periodIntervalInHours);
        $this->endOfLastPeriod = $this->startOfCurrentPeriod->copy()->subSecond();
        $this->startOfLastPeriod = $this->endOfLastPeriod->copy()->subHours($this->periodIntervalInHours);
        $this->primaryAttendanceSlug = app(PrimaryAttendance::class)->getMorphClass();
        $this->secondaryAttendanceSlug = app(SecondaryAttendance::class)->getMorphClass();
    }

    /**
     * GET api/dashboard/removals
     *
     * Remoção dos pacientes das últimas 12 horas
     */
    public function fetchRemovals(DashboardRequest $request): JsonResponse
    {
        if (Cache::has("dashboard.$request->urc_id.removals")) {
            return response()->json(Cache::get("dashboard.$request->urc_id.removals"));
        }

        $removals = Attendance::withoutGlobalScope(UrcScope::class)
            ->where('urc_id', $request->urc_id)
            ->select(['id', 'last_status_updated_at'])
            ->whereBetween('last_status_updated_at', [$this->startOfLastPeriod, $this->endOfCurrentPeriod])
            ->where(function (EloquentBuilder $query) {
                $query->where(function (EloquentBuilder $query) {
                    $query->whereHasMorph('attendable', [$this->primaryAttendanceSlug], fn (EloquentBuilder $q) => $q->withoutGlobalScope(AttendanceScope::class))
                        ->whereHas('sceneRecording', fn (EloquentBuilder $q) => $q->withoutGlobalScope(UrcScope::class)->whereJsonContains('conduct_types', 'Atendimento com Remoção'));
                })
                    ->orWhereHasMorph('attendable', [$this->secondaryAttendanceSlug], fn (EloquentBuilder $q) => $q->withoutGlobalScope(AttendanceScope::class));
            })
            ->where('attendance_status_id', AttendanceStatusEnum::COMPLETED)
            ->get()
            ->mapToGroups(function ($item) {
                $group = \Carbon\Carbon::parse($item->last_status_updated_at)->isBetween($this->startOfCurrentPeriod, $this->endOfCurrentPeriod) ? 'current_period' : 'last_period';

                return [$group => $item];
            });

        $currentPeriod = $removals['current_period'] ?? [];
        $lastPeriod = $removals['last_period'] ?? [];
        $currentPeriodCount = count($currentPeriod);
        $lastPeriodCount = count($lastPeriod);

        $data = [
            'last_period_count' => $lastPeriodCount,
            'current_period_count' => $currentPeriodCount,
            'last_period' => $lastPeriod,
            'current_period' => $currentPeriod,
            'change' => formatPercentageChange($lastPeriodCount, $currentPeriodCount),
        ];

        Cache::put("dashboard.$request->urc_id.removals", $data);

        return response()->json($data);
    }

    /**
     * GET api/dashboard/attendance-base-statuses
     *
     * Quantidade de atendimentos por status
     */
    public function attendanceBaseStatuses(AttendanceBaseStatusesRequest $request): JsonResponse
    {
        $query = DB::table('attendances')
            ->when(!$request->get('totem_dashboard'), function (Builder $query) use ($request) {
                $query->groupBy('attendance_status_id')
                    ->select('attendance_status_id', DB::raw('count(*) as total'))
                    ->where('urc_id', $request->urc_id);
            })
            ->when($request->get('totem_dashboard'), function (Builder $query) {
                $query->select(
                    DB::raw('SUM(case when attendance_status_id = ' . AttendanceStatusEnum::AWAITING_MEDICAL_REGULATION->value . ' then 1 end) as awaiting_medical_regulation'),
                    DB::raw('SUM(case when attendance_status_id = ' . AttendanceStatusEnum::AWAITING_VEHICLE_COMMITMENT->value . ' then 1 end) as awaiting_vehicle_commitment'),
                );
            });

        $results = $request->get('totem_dashboard') ? $query->first() : $query->get();

        return response()->json($results);
    }

    /**
     * GET api/dashboard/committed-vehicles
     *
     * Quantidade de viaturas empenhadas
     */
    public function fetchCommittedVehicles(DashboardRequest $request): JsonResponse
    {
        $vehiclesCount = DB::table('radio_operations')->select(
            DB::raw("SUM(case when arrived_to_site_at is not null and left_from_site_at is null = '1' then 1 end) as count_consultas")
        )
            ->where('urc_id', $request->urc_id)
            ->get();

        return response()->json($vehiclesCount);
    }

    /**
     * GET api/dashboard/occurrences
     *
     * Retorna as ocorrências de atendimentos por tipo primário e secundário, nas últimas 12 horas
     */
    public function fetchOccurrences(DashboardRequest $request): JsonResponse
    {
        $occurrencesCount = DB::table('attendances')
            ->join('urgency_regulation_centers', 'urgency_regulation_centers.id', '=', 'attendances.urc_id')
            ->whereBetween('last_status_updated_at', [$this->startOfLastPeriod, $this->endOfCurrentPeriod])
            ->when($request->get('urc_id'), function (Builder $query) use ($request) {
                $query->where('urc_id', $request->urc_id);
            })
            ->select('attendable_type', 'last_status_updated_at', 'urgency_regulation_centers.name as urc_name', 'attendances.urc_id')
            ->get()
            ->mapToGroups(function ($item) {
                return [$item->attendable_type => $item];
            });

        $typesDefaults = collect([$this->primaryAttendanceSlug => collect(), $this->secondaryAttendanceSlug => collect()]);
        $periodsDefaults = collect(['current_period' => collect(), 'last_period' => collect()]);

        [$this->primaryAttendanceSlug => $primary, $this->secondaryAttendanceSlug => $secondary] = $occurrencesCount->union($typesDefaults)->all();

        $primaryAttendancePeriods = $primary->mapToGroups(function ($item) {
            $group = \Carbon\Carbon::parse($item->last_status_updated_at)->isBetween($this->startOfCurrentPeriod, $this->endOfCurrentPeriod) ? 'current_period' : 'last_period';

            return [$group => $item];
        })->union($periodsDefaults);

        $secondaryAttendancePeriods = $secondary->mapToGroups(function ($item) {
            $group = \Carbon\Carbon::parse($item->last_status_updated_at)->isBetween($this->startOfCurrentPeriod, $this->endOfCurrentPeriod) ? 'current_period' : 'last_period';

            return [$group => $item];
        })->union($periodsDefaults);

        $primaryAttendanceCurrentPeriodCount = count($primaryAttendancePeriods['current_period']);
        $primaryAttendanceLastPeriodCount = count($primaryAttendancePeriods['last_period']);

        $secondaryAttendanceCurrentPeriodCount = count($secondaryAttendancePeriods['current_period']);
        $secondaryAttendanceLastPeriodCount = count($secondaryAttendancePeriods['last_period']);

        $primaryAttendanceChange = formatPercentageChange($primaryAttendanceLastPeriodCount, $primaryAttendanceCurrentPeriodCount);
        $secondaryAttendanceChange = formatPercentageChange($secondaryAttendanceLastPeriodCount, $secondaryAttendanceCurrentPeriodCount);

        $totalOccurrencesPerUrc = $primaryAttendancePeriods['current_period']->merge($secondaryAttendancePeriods['current_period'])->groupBy('urc_name')->map(function ($items, $urcName) {
            $primaryCount = $items->where('attendable_type', $this->primaryAttendanceSlug)->count();
            $secondaryCount = $items->where('attendable_type', $this->secondaryAttendanceSlug)->count();

            return [
                'urc_name' => $urcName,
                'primary_attendance_count' => $primaryCount,
                'secondary_attendance_count' => $secondaryCount,
            ];
        })->values();

        $data = [
            'primary' => [
                'change' => $primaryAttendanceChange,
                'current_period_count' => $primaryAttendanceCurrentPeriodCount,
                'last_period_count' => $primaryAttendanceLastPeriodCount,
                'current_period' => $primaryAttendancePeriods['current_period'],
                'last_period' => $primaryAttendancePeriods['last_period'],
            ],
            'secondary' => [
                'change' => $secondaryAttendanceChange,
                'current_period_count' => $secondaryAttendanceCurrentPeriodCount,
                'last_period_count' => $secondaryAttendanceLastPeriodCount,
                'current_period' => $secondaryAttendancePeriods['current_period'],
                'last_period' => $secondaryAttendancePeriods['last_period'],
            ],
            'total_occurrences_per_urc' => $totalOccurrencesPerUrc,
        ];

        return response()->json($data);
    }

    /**
     * GET api/dashboard/fleet
     *
     * Retorna a frota de viaturas em tempo real e porcentagem de viaturas disponíveis no momento em relação ao total de viaturas
     */
    public function fleet(FleetRequest $request): JsonResponse
    {
        $data = $request->all();

        $results = DB::table('vehicles')
            ->leftJoin('vehicle_status_histories', function (Builder $query) {
                $query->on('vehicles.id', '=', 'vehicle_status_histories.vehicle_id')
                    ->whereRaw('vehicle_status_histories.id = (SELECT id FROM vehicle_status_histories WHERE vehicle_status_histories.vehicle_id = vehicles.id ORDER BY created_at DESC LIMIT 1)');
            })
            ->rightJoin('bases', 'bases.id', '=', 'vehicles.base_id')
            ->groupBy('bases.vehicle_type_id')
            ->when(!empty($data['only_active']), function ($query) {
                $query->select('bases.vehicle_type_id', DB::raw('count(*) as active'))
                    ->where('vehicle_status_histories.vehicle_status_id', VehicleStatusEnum::ACTIVE);
            })
            ->when(!empty($data['urc_id']), function ($query) use ($data) {
                $query->select('bases.vehicle_type_id', DB::raw('count(*) as active'))->where('bases.urc_id', $data['urc_id']);
            })
            ->when(empty($data['only_active']), function (Builder $query) {
                $query->select([
                    'bases.vehicle_type_id',
                    DB::raw('SUM(case when vehicle_status_histories.vehicle_status_id != ' . VehicleStatusEnum::INACTIVE->value . ' then 1 end) as total'),
                    DB::raw('SUM(case when vehicle_status_histories.vehicle_status_id = ' . VehicleStatusEnum::ACTIVE->value . ' then 1 end) as active'),
                    DB::raw('SUM(case when vehicle_status_histories.vehicle_status_id = ' . VehicleStatusEnum::UNAVAILABLE->value . ' then 1 end) as unavailable'),
                    DB::raw('SUM(case when vehicle_status_histories.vehicle_status_id = ' . VehicleStatusEnum::COMMITTED->value . ' then 1 end) as committed'),
                    DB::raw('SUM(case when vehicle_status_histories.vehicle_status_id = ' . VehicleStatusEnum::IN_MAINTENANCE->value . ' then 1 end) as in_maintenance'),
                    DB::raw('SUM(case when vehicle_status_histories.vehicle_status_id = ' . VehicleStatusEnum::SOLICITED->value . ' then 1 end) as solicited'),
                ]);
            })
            ->get();

        $totalFleet = $results->sum('total');
        $totalAvailableFleet = $results->sum('active');

        $percentage = $totalFleet > 0 ? number_format($totalAvailableFleet / $totalFleet * 100, 2) : 0;

        return response()->json([
            'fleet' => $results,
            'percentage_available' => $percentage,
        ]);
    }

    /**
     * GET api/dashboard/retained-patrimonies
     *
     * Retorna os equipamentos retidos em tempo real
     */
    public function retainedPatrimonies(DashboardRequest $request): JsonResponse
    {
        $retainedPatrimonyCount = DB::table('patrimonies')
            ->where('urc_id', $request->urc_id)
            ->where('patrimony_status_id', PatrimonyStatusEnum::RETAINED)
            ->count();

        return response()->json($retainedPatrimonyCount);
    }

    /**
     * GET api/dashboard/attendances-per-professional
     *
     * Retorna os atendimentos por profissional na CRU logada
     */
    public function fetchAttendancesPerProfessional(DashboardRequest $request): ResourceCollection
    {
        $now = Carbon::now();

        $startOfDay = $now->copy()->setTime(7, 0);
        $endOfDay = $now->copy()->setTime(18, 59);

        $isDaytime = $now->between($startOfDay, $endOfDay);

        if ($isDaytime) {
            $startTime = $now->copy()->setTime(7, 0);
            $endTime = $now->copy()->setTime(18, 59);
        } else {
            $startTime = $now->copy()->subDay()->setTime(19, 0);
            $endTime = $now->copy()->setTime(6, 59);
        }

        $results = DB::table('users')
            ->select(
                'users.id',
                'users.name',
                'place_management.name as place_name',
                'roles.name as current_role_slug',
                DB::raw('COALESCE(count(distinct user_attendances.attendance_id), 0) as total_attendances')
            )
            ->leftJoin('user_attendances', function ($join) use ($startTime, $endTime) {
                $join->on('users.id', '=', 'user_attendances.user_id')
                    ->whereBetween('user_attendances.created_at', [$startTime, $endTime]);
            })
            ->join('place_management', function ($join) use ($request) {
                $join->on('place_management.user_id', '=', 'users.id')
                    ->where('place_management.urc_id', $request->urc_id);
            })
            ->join('roles', DB::raw('roles.id::varchar'), '=', 'users.current_role')
            ->whereNotNull('users.current_role')
            ->groupBy('users.id', 'place_management.name', 'roles.name')
            ->orderBy('users.name')
            ->paginate(10);

        return AttendancesPerProfessionalResource::collection($results);
    }

    /**
     * GET api/dashboard/occurrence-types-by-attendance-status
     *
     * Retorna as ocorrências por status
     */
    public function fetchOccurrenceTypesByAttendanceStatus(OcurrenceTypesByAttendanceStatusRequest $request): JsonResponse
    {
        $results = DB::table('attendances')
            ->when($request->get('urc_id'), function (Builder $query) use ($request) {
                $query->where('urc_id', $request->urc_id);
            })
            ->groupBy('attendable_type')
            ->where('attendance_status_id', $request->get('attendance_status_id'))
            ->select('attendable_type', DB::raw('count(*) as total'))
            ->get();

        return response()->json($results);
    }

    /**
     * GET api/unauthenticated/dashboard/prank-calls
     *
     * Retorna as ocorrências de trote nas últimas 12 horas
     */
    public function prankCalls(): JsonResponse
    {
        $result = DB::table('tickets')
            ->whereBetween('created_at', [$this->startOfCurrentPeriod, $this->endOfCurrentPeriod])
            ->where('ticket_type_id', TicketTypeEnum::PRANK_CALL->value)
            ->count();

        return response()->json($result);
    }

    /**
     * GET api/unauthenticated/dashboard/average-response-time
     *
     * Retorna a média do tempo de resposta
     *
     * @throws \Exception
     */
    public function averageResponseTime(): JsonResponse
    {
        $results = DB::table('attendance_time_counts')
            ->join('attendances', 'attendances.id', '=', 'attendance_time_counts.attendance_id')
            ->join('tickets', 'tickets.id', '=', 'attendances.ticket_id')
            ->join('urgency_regulation_centers', 'urgency_regulation_centers.id', '=', 'attendances.urc_id')
            ->whereBetween('attendance_time_counts.response_time_measured_at', [$this->startOfLastPeriod, $this->endOfCurrentPeriod])
            ->whereIn('tickets.ticket_type_id', [TicketTypeEnum::PRIMARY_OCCURRENCE->value, TicketTypeEnum::SECONDARY_OCCURRENCE->value])
            ->groupBy('attendances.urc_id', 'urgency_regulation_centers.name', 'attendances.attendable_type', 'attendance_time_counts.id')
            ->select([
                'attendances.urc_id as urc_id',
                'urgency_regulation_centers.name as urc_name',
                'attendances.attendable_type as attendable_type',
                'attendance_time_counts.*',
                DB::raw('AVG(CASE WHEN tickets.ticket_type_id = ' . TicketTypeEnum::PRIMARY_OCCURRENCE->value . ' THEN response_time END) as primary_attendance_average_response_time'),
                DB::raw('AVG(CASE WHEN tickets.ticket_type_id = ' . TicketTypeEnum::SECONDARY_OCCURRENCE->value . ' THEN response_time END) as secondary_attendance_average_response_time'),
            ])
            ->get()
            ->mapToGroups(function ($item) {
                return [$item->attendable_type => $item];
            });

        $typesDefaults = collect([$this->primaryAttendanceSlug => collect(), $this->secondaryAttendanceSlug => collect()]);
        $periodsDefaults = collect(['current_period' => collect(), 'last_period' => collect()]);

        [$this->primaryAttendanceSlug => $primary, $this->secondaryAttendanceSlug => $secondary] = $results->union($typesDefaults)->all();

        $primaryAttendancePeriods = $primary->mapToGroups(function ($item) {
            $group = Carbon::parse($item->response_time_measured_at)->isBetween($this->startOfCurrentPeriod, $this->endOfCurrentPeriod) ? 'current_period' : 'last_period';

            return [$group => $item];
        })->union($periodsDefaults);

        $secondaryAttendancePeriods = $secondary->mapToGroups(function ($item) {
            $group = Carbon::parse($item->response_time_measured_at)->isBetween($this->startOfCurrentPeriod, $this->endOfCurrentPeriod) ? 'current_period' : 'last_period';

            return [$group => $item];
        })->union($periodsDefaults);

        $currentPeriodPrimaryAttendanceAverage = $primaryAttendancePeriods['current_period']->avg('response_time') ?? 0;
        $currentPeriodSecondaryAttendanceAverage = $secondaryAttendancePeriods['current_period']->avg('response_time') ?? 0;

        $lastPeriodPrimaryAttendanceAverage = $primaryAttendancePeriods['last_period']->avg('response_time') ?? 0;
        $lastPeriodSecondaryAttendanceAverage = $secondaryAttendancePeriods['last_period']->avg('response_time') ?? 0;

        $primaryAttendanceChange = formatPercentageChange($lastPeriodPrimaryAttendanceAverage, $currentPeriodPrimaryAttendanceAverage);
        $secondaryAttendanceChange = formatPercentageChange($lastPeriodSecondaryAttendanceAverage, $currentPeriodSecondaryAttendanceAverage);

        $primaryAndSecondaryAttendancePeriods = $primaryAttendancePeriods['current_period']->merge($secondaryAttendancePeriods['current_period']);

        $averagePerUrc = $primaryAndSecondaryAttendancePeriods->groupBy('urc_id')->map(function ($items) {
            $primaryAverage = $items->where('attendable_type', $this->primaryAttendanceSlug)->avg('response_time');
            $secondaryAverage = $items->where('attendable_type', $this->secondaryAttendanceSlug)->avg('response_time');

            return [
                'urc_name' => $items->first()->urc_name,
                'primary_attendance_average_response_time' => $this->formatTimeDuration($primaryAverage),
                'secondary_attendance_average_response_time' => $this->formatTimeDuration($secondaryAverage),
            ];
        })->values();

        $data = [
            'primary_attendance_average' => $this->formatTimeDuration($primaryAttendancePeriods['current_period']->avg('response_time')),
            'primary_attendance_change' => $primaryAttendanceChange,
            'secondary_attendance_average' => $this->formatTimeDuration($secondaryAttendancePeriods['current_period']->avg('response_time')),
            'secondary_attendance_change' => $secondaryAttendanceChange,
            'per_urc' => $averagePerUrc,
        ];

        return response()->json($data);
    }

    /**
     * GET api/unauthenticated/dashboard/occurrences-per-diagnostic-hypothesis
     *
     * Retorna as ocorrências que registraram as seguintes hipóteses diagnósticas nas últimas 24 horas: 'ACIDENTE VASCULAR CEREBRAL (AVC)', 'INFARTO AGUDO DO MIOCÁRDIO (IAM)', 'Outros'
     */
    public function occurrencesPerDiagnosticHypothesis(): JsonResponse
    {
        $results = DB::table('attendances')
            ->leftJoin('scene_recordings', function ($join) {
                $join->on('attendances.id', '=', 'scene_recordings.attendance_id')
                    ->whereRaw('scene_recordings.id = (SELECT id FROM scene_recordings WHERE scene_recordings.attendance_id = attendances.id ORDER BY created_at DESC LIMIT 1)');
            })
            ->join('medical_regulations', function ($join) {
                $join->on('attendances.id', '=', 'medical_regulations.attendance_id')
                    ->whereRaw('medical_regulations.id = (SELECT id FROM medical_regulations WHERE medical_regulations.attendance_id = attendances.id ORDER BY created_at DESC LIMIT 1)');
            })
            ->join('form_diagnostic_hypotheses', function ($join) {
                $join->on('scene_recordings.id', '=', 'form_diagnostic_hypotheses.form_id')
                    ->orOn(function ($join) {
                        $join->on('medical_regulations.id', '=', 'form_diagnostic_hypotheses.form_id')
                            ->whereNull('scene_recordings.id');
                    });
            })
            ->join('diagnostic_hypotheses', function ($join) {
                $join->on('form_diagnostic_hypotheses.diagnostic_hypothesis_id', '=', 'diagnostic_hypotheses.id')
                    ->whereRaw('diagnostic_hypotheses.id = (SELECT id FROM diagnostic_hypotheses WHERE diagnostic_hypotheses.id = form_diagnostic_hypotheses.diagnostic_hypothesis_id)');
            })
            ->whereBetween('last_status_updated_at', [$this->startOfLastPeriod, $this->endOfCurrentPeriod])
            ->select(
                DB::raw("SUM(CASE WHEN diagnostic_hypotheses.name = 'ACIDENTE VASCULAR CEREBRAL (AVC)' THEN 1 ELSE 0 END) as cva"),
                DB::raw("SUM(CASE WHEN diagnostic_hypotheses.name IN ('INFARTO AGUDO DO MIOCÁRDIO (IAM)', 'IAM') THEN 1 ELSE 0 END) as ami"),
                DB::raw("SUM(CASE WHEN diagnostic_hypotheses.name NOT IN ('ACIDENTE VASCULAR CEREBRAL (AVC)', 'INFARTO AGUDO DO MIOCÁRDIO (IAM)', 'IAM') THEN 1 ELSE 0 END) as others")
            )
            ->first();

        return response()->json($results);
    }
}
