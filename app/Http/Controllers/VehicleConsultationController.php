<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexAttendanceVehicleConsultationRequest;
use App\Http\Resources\VehicleConsultationResource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;
use App\Http\Resources\VehicleAverageResponseTimeResource;
use App\Http\Resources\VehicleConsultationResource;

#[Group(name: 'Relatórios', description: 'Seção responsável pela gestão de relatórios')]
#[Subgroup(name: 'Consulta de Indicadores de VTR')]
class VehicleConsultationController extends Controller
{
    /**
     * GET api/attendance/indicator/vehicles
     *
     * Retorna uma lista páginada dos indicadores das ocorrências por tipo de veículos (filtrados ou não).
     */
    public function index(IndexAttendanceVehicleConsultationRequest $request): ResourceCollection|JsonResponse
    {
        $data = $request->validated();
        $query = DB::table('tickets as t')
            ->where('t.urc_id', '=', auth()->user()->urc_id)
            ->when(isset($data['start_date'], $data['end_date']), function ($query) use ($data) {
                $query->whereBetween('t.opening_at', [Carbon::create($data['start_date'])->startOfDay(), Carbon::create($data['end_date'])->endOfDay()]);
            })
            ->join('attendances as a', 'a.ticket_id', '=', 't.id')
            ->join('patients as p', 'p.id', '=', 'a.patient_id')
            ->when(isset($data['initial_birth_date'], $data['final_birth_date'], $data['time_unit_id']), function ($query) use ($data) {
                $query->whereBetween('p.age', [$data['initial_birth_date'], $data['final_birth_date']])->where('p.time_unit_id', $data['time_unit_id']);
            })
            ->leftJoin(
                DB::raw('(SELECT DISTINCT ON (vsh2.attendance_id) *
                            FROM vehicle_status_histories vsh2
                            WHERE vsh2.base_id IS NOT NULL
                            ORDER BY vsh2.attendance_id, vsh2.created_at DESC) AS vsh'),
                'vsh.attendance_id',
                '=',
                'a.id',
            )
            ->leftJoin('bases as b', 'b.id', '=', 'vsh.base_id')
            ->leftJoin('regional_groups as rg', 'rg.id', '=', 'b.regional_group_id')
            ->when(!empty($data['regional_group_id']), function ($query) use ($data) {
                $query->whereIn('b.regional_group_id', $data['regional_group_id']);
            })
            ->leftJoin('vehicles as v', 'v.id', '=', 'vsh.vehicle_id')
            ->leftJoin('vehicle_types as vt', 'vt.id', '=', 'v.vehicle_type_id')
            ->join(
                DB::raw('(SELECT DISTINCT ON (fdh2.attendance_id) *
                            FROM form_diagnostic_hypotheses fdh2
                            ORDER BY fdh2.attendance_id, fdh2.created_at DESC) AS fdh'),
                'fdh.attendance_id',
                '=',
                'a.id',
            )
            ->join('diagnostic_hypotheses as dh', 'dh.id', '=', 'fdh.diagnostic_hypothesis_id')

            ->where('dh.name', '=', 'IAM');

        $query
            ->select([DB::raw('vt.name as vt_type'), DB::raw("COUNT(*) FILTER (WHERE a.attendable_type = 'primary_attendance') as primary_attendances"), DB::raw("COUNT(*) FILTER (WHERE a.attendable_type = 'secondary_attendance') as secondary_attendances"), DB::raw('COUNT(*) as total_attendances')])
            ->groupBy('vt.name')
            ->orderByDesc('total_attendances');

        $results = $request->get('list_all') ? $query->get() : $query->paginate($request->validated('per_page', 15));

        return VehicleConsultationResource::collection($results);
    }

    /**
     * GET api/attendance/indicator/vehicles/average
     *
     * Retorna uma lista páginada dos indicadores (médias em horas) das ocorrências por tipo de veículos (filtrados ou não).
     */
    public function indexGroupedByCruAndVehicleType(IndexAttendanceVehicleConsultationRequest $request): JsonResponse
    {
        $data = $request->validated();

        $query = DB::table('tickets as t')
            ->where('t.urc_id', '=', auth()->user()->urc_id)
            ->when(isset($data['start_date'], $data['end_date']), function ($query) use ($data) {
                $query->whereBetween('t.opening_at', [Carbon::create($data['start_date'])->startOfDay(), Carbon::create($data['end_date'])->endOfDay()]);
            })
            ->join('attendances as a', 'a.ticket_id', '=', 't.id')
            ->join('patients as p', 'p.id', '=', 'a.patient_id')
            ->when(isset($data['initial_birth_date'], $data['final_birth_date'], $data['time_unit_id']), function ($query) use ($data) {
                $query->whereBetween('p.age', [$data['initial_birth_date'], $data['final_birth_date']])->where('p.time_unit_id', $data['time_unit_id']);
            })
            ->leftJoin(
                DB::raw('(SELECT DISTINCT ON (vsh2.attendance_id) *
                            FROM vehicle_status_histories vsh2
                            WHERE vsh2.base_id IS NOT NULL
                            ORDER BY vsh2.attendance_id, vsh2.created_at DESC) AS vsh'),
                'vsh.attendance_id',
                '=',
                'a.id',
            )
            ->leftJoin('bases as b', 'b.id', '=', 'vsh.base_id')
            ->leftJoin('regional_groups as rg', 'rg.id', '=', 'b.regional_group_id')
            ->when(!empty($data['regional_group_id']), function ($query) use ($data) {
                $query->whereIn('b.regional_group_id', $data['regional_group_id']);
            })
            ->leftJoin('vehicles as v', 'v.id', '=', 'vsh.vehicle_id')
            ->leftJoin('vehicle_types as vt', 'vt.id', '=', 'v.vehicle_type_id')
            ->join(
                DB::raw('(SELECT DISTINCT ON (fdh2.attendance_id) *
                            FROM form_diagnostic_hypotheses fdh2
                            ORDER BY fdh2.attendance_id, fdh2.created_at DESC) AS fdh'),
                'fdh.attendance_id',
                '=',
                'a.id',
            )
            ->join('diagnostic_hypotheses as dh', 'dh.id', '=', 'fdh.diagnostic_hypothesis_id')
            ->leftJoin('radio_operations as ro', 'ro.attendance_id', '=', 'a.id')
            ->where('dh.name', '=', 'IAM');

        $query
            ->select([
                DB::raw('vt.name as vt_type'),
                DB::raw("ROUND(AVG(CASE
            WHEN a.attendable_type = 'primary_attendance'
                AND ro.arrived_to_site_at IS NOT NULL
                AND t.opening_at IS NOT NULL
            THEN EXTRACT(EPOCH FROM (ro.arrived_to_site_at - t.opening_at)) / 3600
            ELSE NULL
        END), 2) as primary_avg_hours"),
                DB::raw("ROUND(AVG(CASE
            WHEN a.attendable_type = 'secondary_attendance'
                AND ro.vehicle_released_at IS NOT NULL
                AND ro.vehicle_requested_at IS NOT NULL
            THEN EXTRACT(EPOCH FROM (ro.vehicle_released_at - ro.vehicle_requested_at)) / 3600
            ELSE NULL
        END), 2) as secondary_avg_hours"),
                DB::raw("ROUND(AVG(CASE
            WHEN a.attendable_type = 'primary_attendance'
                AND ro.arrived_to_site_at IS NOT NULL
                AND t.opening_at IS NOT NULL
            THEN EXTRACT(EPOCH FROM (ro.arrived_to_site_at - t.opening_at)) / 3600
            WHEN a.attendable_type = 'secondary_attendance'
                AND ro.vehicle_released_at IS NOT NULL
                AND ro.vehicle_requested_at IS NOT NULL
            THEN EXTRACT(EPOCH FROM (ro.vehicle_released_at - ro.vehicle_requested_at)) / 3600
            ELSE NULL
        END), 2) as total_avg_hours"),
            ])
            ->groupBy('vt.name')
            ->orderByDesc('total_avg_hours');

        $results = $query->get();

        $totalSum = 0;
        $qtdTypes = 0;

        foreach ($results as $row) {
            if (!is_null($row->total_avg_hours)) {
                $totalSum += $row->total_avg_hours;
                $qtdTypes++;
            }
        }

        $overallAverage = $qtdTypes > 0 ? round($totalSum / $qtdTypes, 2) : 0;

        return response()->json([
            'data' => $results,
            'overall_average' => $overallAverage,
        ]);
    }
}