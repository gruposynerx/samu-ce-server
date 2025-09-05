<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexAttendanceIAMConsultationRequest;
use App\Http\Resources\IAMConsultationResource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group(name: 'Relatórios', description: 'Seção responsável pela gestão de relatórios')]
#[Subgroup(name: 'Consulta de Indicadores de Ocorrência - IAM')]

class IAMConsultationController extends Controller
{
    /**
     * GET api/attendance/indicator/iam
     *
     * Retorna uma lista páginada dos indicadores de ocorrências de iam (filtrados ou não).
     */
    public function index(IndexAttendanceIAMConsultationRequest $request): ResourceCollection|JsonResponse
    {
        $data = $request->validated();

        $query = DB::table('tickets as t')
            ->where('t.urc_id', '=', auth()->user()->urc_id)
            ->when(isset($data['start_date'], $data['end_date']), function ($query) use ($data) {
                $query->whereBetween(
                    't.opening_at',
                    [Carbon::create($data['start_date'])->startOfDay(), Carbon::create($data['end_date'])->endOfDay()]
            );
            })
            ->join('attendances as a', 'a.ticket_id', '=', 't.id')
            ->join('patients as p', 'p.id', '=', 'a.patient_id')
            ->when(isset($data['initial_birth_date'], $data['final_birth_date'], $data['time_unit_id']), function ($query) use ($data) {
                $query->whereBetween("p.age", [
                    $data['initial_birth_date'],
                    $data['final_birth_date']
                ])->where('p.time_unit_id', $data['time_unit_id']);
            })
            ->join('urgency_regulation_centers as urc', 'urc.id', '=', 'a.urc_id')
            ->join('cities as c', 'c.id', '=', 't.city_id')
            ->when(!empty($data['cities']), function ($query) use ($data) {
                $query->whereIn('c.id', $data['cities']);
            })
            ->leftJoin('secondary_attendances as sa', 'sa.id', '=', 'a.attendable_id')
            ->leftJoin('primary_attendances as pa', 'pa.id', '=', 'a.attendable_id')
            ->leftJoin('units as unitsecorigin', 'unitsecorigin.id', '=', 'sa.unit_origin_id')
            ->leftJoin('cities as citysecorigin', 'citysecorigin.id', '=', 'unitsecorigin.city_id')
            ->leftJoin('units as unitsecdestination', 'unitsecdestination.id', '=', 'sa.unit_destination_id')
            ->leftJoin('cities as citysecdestination', 'citysecdestination.id', '=', 'unitsecdestination.city_id')
            ->leftJoin('units as unitpridestination', 'unitpridestination.id', '=', 'pa.unit_destination_id')
            ->leftJoin('cities as citypridestination', 'citypridestination.id', '=', 'unitpridestination.city_id')
            ->when(!empty($data['units_origin']), function ($query) use ($data) {
                $query->whereIn('unitsecorigin.id', $data['units_origin']);
            })
            ->when(!empty($data['units_destination']), function ($query) use ($data) {
                $query->where(function ($q) use ($data) {
                    $q->whereIn('unitsecdestination.id', $data['units_destination'])
                        ->orWhereIn('unitpridestination.id', $data['units_destination']);
                });
            })
            ->leftJoin(
                DB::raw('(SELECT DISTINCT ON (vsh2.attendance_id) *
                            FROM vehicle_status_histories vsh2
                            WHERE vsh2.base_id IS NOT NULL
                            ORDER BY vsh2.attendance_id, vsh2.created_at DESC) AS vsh'),
                'vsh.attendance_id',
                '=',
                'a.id'
            )
            ->leftJoin('bases as b', 'b.id', '=', 'vsh.base_id')
            ->when(!empty($data['bases']), function ($query) use ($data) {
                $query->whereIn('b.id', $data['bases']);
            })
            ->leftJoin('regional_groups as rg', 'rg.id', '=', 'b.regional_group_id')
            ->leftJoin('vehicles as v', 'v.id', '=', 'vsh.vehicle_id')
            ->leftJoin('vehicle_types as vt', 'vt.id', '=', 'v.vehicle_type_id')
            ->join(
                DB::raw('(SELECT DISTINCT ON (fdh2.attendance_id) *
                        FROM form_diagnostic_hypotheses fdh2
                        ORDER BY fdh2.attendance_id, fdh2.created_at DESC, fdh2.id DESC) AS fdh'),
                'fdh.attendance_id',
                '=',
                'a.id'
            )
            ->when(!empty($data['thrombolytic_recommended']), function ($query) use ($data) {
                $query->whereIn('fdh.recommended', $data['thrombolytic_recommended']);
            })
            ->when(!empty($data['thrombolytic_applied']), function ($query) use ($data) {
                $query->whereIn('fdh.applied', $data['thrombolytic_applied']);
            })
            ->where(function ($query) {
            $query->whereNotNull('fdh.recommended')
                ->orWhereNotNull('fdh.applied');
            })
            ->join('diagnostic_hypotheses as dh', 'dh.id', '=', 'fdh.diagnostic_hypothesis_id')
            ->where('dh.name', '=', 'IAM')
            ->join('nature_types as nt', 'nt.id', '=', 'fdh.nature_type_id');

        $query->select([
            'a.id',
            DB::raw("t.ticket_sequence_per_urgency_regulation_center as ticket_sequence"),
            DB::raw("a.attendance_sequence_per_ticket as attendance_patient_sequence"),
            DB::raw("t.opening_at as opening_at"),
            DB::raw("p.name as patient"),
            DB::raw("p.age as age"),
            DB::raw("p.gender_code as gender"),
            DB::raw("p.time_unit_id as time_unit_id"),
            DB::raw("c.name as city"),
            DB::raw("urc.name as cru"),
            DB::raw("nt.name as nature"),
            DB::raw("dh.name as hd"),
            DB::raw("rg.name as regional_group_name"),
            DB::raw("b.name as base_name"),
            DB::raw("(concat(vt.name, ' ', v.code)) as vtr"),
            DB::raw("(CASE WHEN fdh.recommended = '1' THEN false WHEN fdh.recommended = '2' THEN true ELSE NULL END) as recommended"),
            DB::raw("(CASE WHEN fdh.applied = '1' THEN false WHEN fdh.applied = '2' THEN true ELSE NULL END) as applied"),
            DB::raw("(CASE
                WHEN a.attendable_type = 'primary_attendance' THEN concat(c.name, ' - ', pa.neighborhood)
                WHEN a.attendable_type = 'secondary_attendance' THEN concat(citysecorigin.name, ' - ', unitsecorigin.name)
                ELSE NULL END) as unit_origin_name"),
            DB::raw("(CASE
                WHEN a.attendable_type = 'primary_attendance' THEN concat(citypridestination, ' - ', unitpridestination.name)
                WHEN a.attendable_type = 'secondary_attendance' THEN concat(citysecdestination.name, ' - ', unitsecdestination.name)
                ELSE NULL END) as unit_destination_name"),
        ])
            ->orderByDesc('t.opening_at');

        $results = $request->get('list_all')
            ? $query->get()
            : $query->paginate($request->validated('per_page', 15));

        return IAMConsultationResource::collection($results);
    }

    public function dashboard(IndexAttendanceIAMConsultationRequest $request): ResourceCollection|JsonResponse
    {
        $data = $request->validated();

        $query = DB::table('tickets as t')
            ->where('t.urc_id', '=', auth()->user()->urc_id)
            ->when(isset($data['start_date'], $data['end_date']), function ($query) use ($data) {
                $query->whereBetween(
                    't.opening_at',
                    [Carbon::create($data['start_date'])->startOfDay(), Carbon::create($data['end_date'])->endOfDay()]
                );
            })
            ->join('attendances as a', 'a.ticket_id', '=', 't.id')
            ->join('patients as p', 'p.id', '=', 'a.patient_id')
            ->when(isset($data['initial_birth_date'], $data['final_birth_date'], $data['time_unit_id']), function ($query) use ($data) {
                $query->whereBetween("p.age", [
                    $data['initial_birth_date'],
                    $data['final_birth_date']
                ])->where('p.time_unit_id', $data['time_unit_id']);
            })
            ->join('urgency_regulation_centers as urc', 'urc.id', '=', 'a.urc_id')
            ->join('cities as c', 'c.id', '=', 't.city_id')
            ->when(!empty($data['cities']), function ($query) use ($data) {
                $query->whereIn('c.id', $data['cities']);
            })
            ->leftJoin('secondary_attendances as sa', 'sa.id', '=', 'a.attendable_id')
            ->leftJoin('primary_attendances as pa', 'pa.id', '=', 'a.attendable_id')
            ->leftJoin('units as unitsecorigin', 'unitsecorigin.id', '=', 'sa.unit_origin_id')
            ->leftJoin('cities as citysecorigin', 'citysecorigin.id', '=', 'unitsecorigin.city_id')
            ->leftJoin('units as unitsecdestination', 'unitsecdestination.id', '=', 'sa.unit_destination_id')
            ->leftJoin('cities as citysecdestination', 'citysecdestination.id', '=', 'unitsecdestination.city_id')
            ->leftJoin('units as unitpridestination', 'unitpridestination.id', '=', 'pa.unit_destination_id')
            ->leftJoin('cities as citypridestination', 'citypridestination.id', '=', 'unitpridestination.city_id')
            ->when(!empty($data['units_origin']), function ($query) use ($data) {
                $query->whereIn('unitsecorigin.id', $data['units_origin']);
            })
            ->when(!empty($data['units_destination']), function ($query) use ($data) {
                $query->where(function ($q) use ($data) {
                    $q->whereIn('unitsecdestination.id', $data['units_destination'])
                        ->orWhereIn('unitpridestination.id', $data['units_destination']);
                });
            })
            ->leftJoin(
                DB::raw('(SELECT DISTINCT ON (vsh2.attendance_id) *
                            FROM vehicle_status_histories vsh2
                            WHERE vsh2.base_id IS NOT NULL
                            ORDER BY vsh2.attendance_id, vsh2.created_at DESC) AS vsh'),
                'vsh.attendance_id',
                '=',
                'a.id'
            )
            ->leftJoin('bases as b', 'b.id', '=', 'vsh.base_id')
            ->when(!empty($data['bases']), function ($query) use ($data) {
                $query->whereIn('b.id', $data['bases']);
            })
            ->leftJoin('regional_groups as rg', 'rg.id', '=', 'b.regional_group_id')
            ->leftJoin('vehicles as v', 'v.id', '=', 'vsh.vehicle_id')
            ->leftJoin('vehicle_types as vt', 'vt.id', '=', 'v.vehicle_type_id')
            ->join(
                DB::raw('(SELECT DISTINCT ON (fdh2.attendance_id) *
                             FROM form_diagnostic_hypotheses fdh2
                             ORDER BY fdh2.attendance_id, fdh2.created_at DESC, fdh2.id DESC) AS fdh'),
                'fdh.attendance_id',
                '=',
                'a.id'
            )
            ->when(!empty($data['thrombolytic_recommended']), function ($query) use ($data) {
                $query->whereIn('fdh.recommended', $data['thrombolytic_recommended']);
            })
            ->when(!empty($data['thrombolytic_applied']), function ($query) use ($data) {
                $query->whereIn('fdh.applied', $data['thrombolytic_applied']);
            })
            ->where(function ($query) {
            $query->whereNotNull('fdh.recommended')
                ->orWhereNotNull('fdh.applied');
            })
            ->join('diagnostic_hypotheses as dh', 'dh.id', '=', 'fdh.diagnostic_hypothesis_id')
            ->where('dh.name', '=', 'IAM')
            ->join('nature_types as nt', 'nt.id', '=', 'fdh.nature_type_id');

        $periods = $this->getPeriods($data['start_date'], $data['end_date']);
        $resultAggregatedAttendancesByPeriod = (clone $query)
            ->selectRaw("DATE_PART('year', t.opening_at at time zone 'utc' at time zone 'America/Fortaleza') AS year, DATE_PART('month', t.opening_at at time zone 'utc' at time zone 'America/Fortaleza') AS month, a.attendable_type, COUNT(*) AS total")
            ->groupBy(DB::raw("DATE_PART('year', t.opening_at at time zone 'utc' at time zone 'America/Fortaleza')"), DB::raw("DATE_PART('month', t.opening_at at time zone 'utc' at time zone 'America/Fortaleza')"), 'a.attendable_type')
            ->get();

        $aggregatedAttendancesByPeriodAndAttendanceType = $periods->map(function ($period) use ($resultAggregatedAttendancesByPeriod) {
            $year = $period['year'];
            $month = $period['month'];
            $label = $period['label'];

            // Filter data matching this period
            $periodData = $resultAggregatedAttendancesByPeriod->filter(function ($item) use ($year, $month) {
                return $item->year == $year && $item->month == $month;
            });

            // Group attendance types
            $subGroups = collect(['primary_attendance', 'secondary_attendance'])->map(function ($type) use ($periodData) {
                $entry = $periodData->firstWhere('attendable_type', $type);

                return [
                    'key' => match ($type) {
                        'primary_attendance' => 'Ocorrência primária',
                        'secondary_attendance' => 'Ocorrência secundária',
                        default => 'Outros',
                    },
                    'value' => $entry->total ?? 0,
                ];
            });

            // Optional: include "Outros" if there are unexpected types
            $otherTypes = $periodData->filter(function ($item) {
                return !in_array($item->attendable_type, ['primary_attendance', 'secondary_attendance']);
            });

            foreach ($otherTypes as $other) {
                $subGroups->push([
                    'key' => 'Outros',
                    'value' => $other->total,
                ]);
            }

            return [
                'key' => $label,
                'value' => $subGroups->sum('value'),
                'subGroups' => $subGroups->values()->toArray(),
            ];
        })->values()->toArray();

        $aggregatedAttendancesByAttendanceType = (clone $query)->groupBy("a.attendable_type")->select('a.attendable_type', DB::raw('count(*) as total'))->get()->map(function ($items, $attendable_type) {
            return [
                'key' => match ($items->attendable_type) {
                    "primary_attendance" => "Ocorrência primária",
                    "secondary_attendance" => "Ocorrência secundária",
                    default => "Outros",
                },
                'value' => $items->total,
            ];
        })->sortBy('key')->values()->toArray();

        $aggregatedAttendancesByPriority = (clone $query)->groupBy("a.attendable_type")->select('a.attendable_type', DB::raw('count(*) as total'))->get()->map(function ($items, $attendable_type) {
            return [
                'key' => match ($items->attendable_type) {
                    "primary_attendance" => "Ocorrência primária",
                    "secondary_attendance" => "Ocorrência secundária",
                    default => "Outros",
                },
                'value' => $items->total,
            ];
        })->sortBy('key')->values()->toArray();

        $aggregatedAttendancesByRegionalGroupAndAttendanceType = (clone $query)->groupBy(["rg.name", "a.attendable_type"])->select(DB::raw('rg.name as regional_group_name'), 'a.attendable_type', DB::raw('count(*) as total'))->get()->groupBy('regional_group_name')->map(function ($items, $regionalGroup) {
            return [
                'key' => !empty($regionalGroup) ? $regionalGroup : "Não informado",
                'value' => $items->sum('total'),
                'subGroups' => $items->map(function ($subItem) {
                    return [
                        'key' => match ($subItem->attendable_type) {
                            "primary_attendance" => "Ocorrência primária",
                            "secondary_attendance" => "Ocorrência secundária",
                            default => "Outros",
                        },
                        'value' => $subItem->total,
                    ];
                })->values()->toArray(),
            ];
        })->sortByDesc('value')->values()->toArray();

        $aggregatedThrombolyticUsagesByRegionalGroup = (clone $query)->groupBy(["rg.name", "fdh.applied"])->select(DB::raw('rg.name as regional_group_name'), 'fdh.applied', DB::raw('count(*) as total'))->get()->groupBy('regional_group_name')->map(function ($items, $regionalGroup) {
            return [
                'key' => !empty($regionalGroup) ? $regionalGroup : "Não informado",
                'value' => $items->sum('total'),
                'subGroups' => $items->map(function ($subItem) {
                    return [
                        'key' => match ($subItem->applied) {
                            "2" => "Aplicado",
                            "1" => "Não aplicado",
                            default => "Não informado",
                        },
                        'value' => $subItem->total,
                    ];
                })->values()->toArray(),
            ];
        })->sortByDesc('value')->values()->toArray();

        $aggregatedAttendancesByBaseAndAttendanceType = (clone $query)->groupBy("b.name", "a.attendable_type")->select(DB::raw('b.name as base_name'), 'a.attendable_type', DB::raw('count(*) as total'))->get()->groupBy('base_name')->map(function ($items, $base) {
            return [
                'key' => !empty($base) ? $base : "Não informado",
                'value' => $items->sum('total'),
                'subGroups' => $items->map(function ($subItem) {
                    return [
                        'key' => match ($subItem->attendable_type) {
                            "primary_attendance" => "Ocorrência primária",
                            "secondary_attendance" => "Ocorrência secundária",
                            default => "Outros",
                        },
                        'value' => $subItem->total,
                    ];
                })->values()->toArray(),
            ];
        })->sortByDesc('value')->values()->toArray();

        $aggregatedThrombolyticUsagesByBase = (clone $query)->groupBy('b.name', 'fdh.applied')->select(DB::raw('b.name as base_name'), 'fdh.applied', DB::raw('count(*) as total'))->get()->groupBy('base_name')->map(function ($items, $base) {
            return [
                'key' => !empty($base) ? $base : "Não informado",
                'value' => $items->sum('total'),
                'subGroups' => $items->map(function ($subItem) {
                    return [
                        'key' => match ($subItem->applied) {
                            "2" => "Aplicado",
                            "1" => "Não aplicado",
                            default => "Não informado",
                        },
                        'value' => $subItem->total,
                    ];
                })->values()->toArray(),
            ];
        })->sortByDesc('value')->values()->toArray();

        $aggregatedAttendancesByCityAndAttendanceType = (clone $query)->groupBy("c.name", "a.attendable_type")->select(DB::raw('c.name as city_name'), 'a.attendable_type', DB::raw('count(*) as total'))->get()->groupBy('city_name')->map(function ($items, $city) {
            return [
                'key' => !empty($city) ? $city : "Não informado",
                'value' => $items->sum('total'),
                'subGroups' => $items->map(function ($subItem) {
                    return [
                        'key' => match ($subItem->attendable_type) {
                            "primary_attendance" => "Ocorrência primária",
                            "secondary_attendance" => "Ocorrência secundária",
                            default => "Outros",
                        },
                        'value' => $subItem->total,
                    ];
                })->values()->toArray(),
            ];
        })->sortByDesc('value')->values()->toArray();

        $aggregatedThrombolyticUsagesByCity = (clone $query)->groupBy("c.name", "fdh.applied")->select(DB::raw('c.name as city_name'), 'fdh.applied', DB::raw('count(*) as total'))->get()->groupBy('city_name')->map(function ($items, $city) {
            return [
                'key' => !empty($city) ? $city : "Não informado",
                'value' => $items->sum('total'),
                'subGroups' => $items->map(function ($subItem) {
                    return [
                        'key' => match ($subItem->applied) {
                            "2" => "Aplicado",
                            "1" => "Não aplicado",
                            default => "Não informado",
                        },
                        'value' => $subItem->total,
                    ];
                })->values()->toArray(),
            ];
        })->sortByDesc('value')->values()->toArray();

        /*
        Neonatal = até 29 dias,
        Lactente = 1 mês a 2 anos,
        Criança = 3 anos a 14 anos,
        Adolescente = 15 anos a 20 anos,
        Adulto = 21 anos a 60 anos,
        Idoso = 61 anos a 91 anos,
        Muito idoso = acima de 90 anos
        */

        $ageGroups = [
            'Muito idoso',
            'Idoso',
            'Adulto',
            'Adolescente',
            'Criança',
            'Lactente',
            'Neonatal',
        ];

        $genders = ['M', 'F', 'O'];

        $combinations = [];
        foreach ($genders as $gender) {
            foreach ($ageGroups as $ageGroup) {
                $combinations[] = [
                    'gender_code' => $gender,
                    'ageGroup' => $ageGroup,
                ];
            }
        }

        $realData = (clone $query)
            ->select([
                'p.gender_code',
                DB::raw("
            CASE 
                WHEN p.time_unit_id = 1 THEN p.age / 365.0
                WHEN p.time_unit_id = 2 THEN p.age / 12.0
                ELSE p.age
            END AS idade_em_anos
        ")
            ])
            ->get()
            ->groupBy(function ($item) {
                $age = $item->idade_em_anos;
                $ageGroup = match (true) {
                    $age <= (29.0 / 365) => 'Neonatal',
                    $age <= 2 => 'Lactente',
                    $age <= 14 => 'Criança',
                    $age <= 20 => 'Adolescente',
                    $age <= 60 => 'Adulto',
                    $age <= 90 => 'Idoso',
                    default => 'Muito idoso',
                };

                return $item->gender_code . '|' . $ageGroup;
            });

        $aggregatedAttendancesByGenderAndAge = collect($combinations)
            ->groupBy('gender_code')
            ->map(function ($ageGroupItems, $gender) use ($realData) {
                $subGroups = collect($ageGroupItems)->map(function ($item) use ($realData) {
                    $key = $item['gender_code'] . '|' . $item['ageGroup'];
                    $count = isset($realData[$key]) ? count($realData[$key]) : 0;

                    return [
                        'key' => $item['ageGroup'],
                        'value' => $count,
                    ];
                });

                return [
                    'key' => $gender,
                    'value' => $subGroups->sum('value'),
                    'subGroups' => $subGroups,
                ];
            })
            ->values();

        $totals = (clone $query)
            ->select([
                DB::raw("COUNT(*) AS total_attendances"),
                DB::raw("COUNT(CASE WHEN fdh.applied = '2' THEN 1 END) AS total_thrombolytics_applied"),
                DB::raw("COUNT(CASE WHEN fdh.applied = '1' THEN 1 END) AS total_thrombolytics_not_applied"),
                DB::raw("
                    AVG(CAST(
                        CASE
                            WHEN p.time_unit_id = 3 THEN
                                CASE
                                    WHEN p.age < 150 THEN p.age
                                    ELSE p.age / 365
                                END
                            WHEN p.time_unit_id = 2 THEN p.age / 12
                            WHEN p.time_unit_id = 1 THEN p.age / 365
                        END
                    AS INTEGER))::decimal(10,2) AS average_patient_age
                "),
            ])->first();

        $dashboard = [
            "totalAttendances"            => $totals->total_attendances ?? 0,
            "totalThrombolyticsApplied"   => $totals->total_thrombolytics_applied ?? 0,
            "totalThrombolyticsNotApplied" => $totals->total_thrombolytics_not_applied ?? 0,
            "averagePatientAge"           => (float) $totals->average_patient_age ?? 0,
            "aggregatedAttendancesByAttendanceType" => $aggregatedAttendancesByAttendanceType,
            "aggregatedAttendancesByPriority" => $aggregatedAttendancesByPriority,
            "aggregatedAttendancesByRegionalGroupAndAttendanceType" => $aggregatedAttendancesByRegionalGroupAndAttendanceType,
            "aggregatedThrombolyticUsagesByRegionalGroup" => $aggregatedThrombolyticUsagesByRegionalGroup,
            "aggregatedAttendancesByBaseAndAttendanceType" => $aggregatedAttendancesByBaseAndAttendanceType,
            "aggregatedThrombolyticUsagesByBase" => $aggregatedThrombolyticUsagesByBase,
            "aggregatedAttendancesByCityAndAttendanceType" => $aggregatedAttendancesByCityAndAttendanceType,
            "aggregatedThrombolyticUsagesByCity" => $aggregatedThrombolyticUsagesByCity,
            "aggregatedAttendancesByPeriodAndAttendanceType" => $aggregatedAttendancesByPeriodAndAttendanceType,
            "aggregatedAttendancesByGenderAndAge" => $aggregatedAttendancesByGenderAndAge
        ];

        return response()->json($dashboard);
    }


    public function getPeriods($startDate, $endDate)
    {
        $startDate = Carbon::create($startDate)->startOfDay()->startOfMonth();
        $endDate = Carbon::create($endDate)->startOfDay()->startOfMonth();
        $periods = collect();
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $periods->push([
                'year' => $current->year,
                'month' => $current->month,
                'label' => $current->format('Y/m'),
            ]);
            $current->addMonth();
        }

        return $periods;
    }
}
