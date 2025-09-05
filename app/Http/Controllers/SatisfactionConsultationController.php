<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Http\Requests\IndexSatisfactionConsultationRequest;

class SatisfactionConsultationController extends Controller
{
    public function indexSatisfactionDashboard(IndexSatisfactionConsultationRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $responses = DB::table('requester_satisfactions as rs')
            ->leftJoin('attendances as a', 'a.id', '=', 'rs.attendance_id')
            ->leftJoin('tickets as t', 't.id', '=', 'a.ticket_id')
            ->leftJoin('cities as c', 'c.id', '=', 't.city_id')
            ->leftJoin('urgency_regulation_centers as urc', 'urc.id', '=', 'a.urc_id')
            ->leftJoin(DB::raw('(
            SELECT DISTINCT ON (attendance_id) *
            FROM vehicle_status_histories
            WHERE base_id IS NOT NULL
            ORDER BY attendance_id, created_at DESC
        ) as vsh'), 'vsh.attendance_id', '=', 'a.id')
            ->leftJoin('bases as b', 'b.id', '=', 'vsh.base_id')
            ->leftJoin('regional_groups as rg', 'rg.id', '=', 'b.regional_group_id')
            ->where('t.ticket_type_id', '=', 1)
            ->when($filters['start_date'] ?? null, fn($q, $date) =>
            $q->where('rs.created_at', '>=', Carbon::parse($date)->timezone('America/Fortaleza')->setTimezone('UTC')))
            ->when($filters['end_date'] ?? null, fn($q, $date) =>
            $q->where('rs.created_at', '<', Carbon::parse($date)->timezone('America/Fortaleza')->setTimezone('UTC')))
            ->when(!empty($filters['cru']), fn($q) => $q->whereIn('urc.id', $filters['cru']))
            ->when(!empty($filters['cities']), fn($q) => $q->whereIn('c.id', $filters['cities']))
            ->when(!empty($filters['bases']), fn($q) => $q->whereIn('b.id', $filters['bases']))
            ->when(!empty($filters['groups_regional']), fn($q) => $q->whereIn('rg.id', $filters['groups_regional']))
            ->select(
                DB::raw('ROUND((rs.scale_attendance_provided_mecs_team + rs.scale_satisfaction_service_offered + rs.scale_telephone_attendance) / 3.0) as scale'),
                'rs.scale_satisfaction_service_offered as scale_service',
                'rs.scale_telephone_attendance as scale_phone',
                'rs.scale_attendance_provided_mecs_team as scale_team',
                'rs.satisfaction_time_spent_phone_id as time_spent_phone',
                'rs.satisfaction_time_ambulance_arrive_id as time_ambulance_arrive',
                'urc.name as cru',
                'b.name as base',
                'rg.name as polo',
                'c.name as city'
            )
            ->get();

        $scaleLabels = [
            5 => 'Muito Satisfeito',
            4 => 'Satisfeito',
            3 => 'Neutro',
            2 => 'Insatisfeito',
            1 => 'Muito Insatisfeito',
        ];

        $totalResponses = $responses->count();

        $totalEvaluatedAttendance = $responses->where('scale', '>=', 1)->count();

        $veryDissatisfiedCount = $responses->where('scale', 1)->count();
        $dissatisfiedCount = $responses->where('scale', 2)->count();
        $neutralCount = $responses->where('scale', 3)->count();
        $satisfiedCount = $responses->where('scale', 4)->count();
        $verySatisfiedCount = $responses->where('scale', 5)->count();

        $totalAttendances = DB::table('attendances as a')
            ->join('tickets as t', 't.id', '=', 'a.ticket_id')
            ->join('cities as c', 'c.id', '=', 't.city_id')
            ->join('urgency_regulation_centers as urc', 'urc.id', '=', 'a.urc_id')
            ->join(DB::raw('(
            SELECT DISTINCT ON (attendance_id) *
            FROM vehicle_status_histories
            WHERE base_id IS NOT NULL
            ORDER BY attendance_id, created_at DESC
        ) as vsh'), 'vsh.attendance_id', '=', 'a.id')
            ->join('bases as b', 'b.id', '=', 'vsh.base_id')
            ->join('regional_groups as rg', 'rg.id', '=', 'b.regional_group_id')
            ->where('t.ticket_type_id', '=', 1)
            ->when($filters['start_date'] ?? null, fn($q, $date) =>
            $q->where('a.created_at', '>=', Carbon::parse($date)->timezone('America/Fortaleza')->setTimezone('UTC')))
            ->when($filters['end_date'] ?? null, fn($q, $date) =>
            $q->where('a.created_at', '<', Carbon::parse($date)->timezone('America/Fortaleza')->setTimezone('UTC')))
            ->when(!empty($filters['cru']), fn($q) => $q->whereIn('urc.id', $filters['cru']))
            ->when(!empty($filters['cities']), fn($q) => $q->whereIn('c.id', $filters['cities']))
            ->when(!empty($filters['bases']), fn($q) => $q->whereIn('b.id', $filters['bases']))
            ->when(!empty($filters['groups_regional']), fn($q) => $q->whereIn('rg.id', $filters['groups_regional']))
            ->distinct()
            ->count('a.id');

        $suggestions = DB::table('requester_satisfactions')
            ->whereNotNull('requester_sugestion')
            ->orderByDesc('created_at')
            ->limit(100)
            ->pluck('requester_sugestion');

        $satisfactionRate = $totalResponses > 0
            ? round((($satisfiedCount + $verySatisfiedCount) * 100) / $totalResponses, 2) : 0;

        $surveyResponseRate = $totalAttendances > 0
            ? round(($totalResponses * 100) / $totalAttendances, 2) : 0;

        $byCategory = [];
        $totalbyCategory = 0;
        $lastKey = array_key_last($scaleLabels);

        foreach ($scaleLabels as $key => $label) {
            $count = $responses->where('scale', $key)->count();
            $percent = $totalResponses > 0 ? round($count * 100 / $totalResponses, 2) : 0;
            if ($key === $lastKey && $totalResponses > 0) {
                $percent = round(100 - $totalbyCategory, 2);
            }
            $totalbyCategory += $percent;
            $byCategory[] = [
                'key' => $label,
                'value' => $percent,
            ];
        }

        $groupedAttendances = $responses->groupBy('cru');
        $attendanceCounts = DB::table('attendances as a')
            ->join('tickets as t', 't.id', '=', 'a.ticket_id')
            ->join('urgency_regulation_centers as urc', 'urc.id', '=', 'a.urc_id')
            ->where('t.ticket_type_id', '=', 1)
            ->when($filters['start_date'] ?? null, fn($q, $d) =>
            $q->where('a.created_at', '>=', Carbon::parse($d)->timezone('America/Fortaleza')->setTimezone('UTC')))
            ->when($filters['end_date'] ?? null, fn($q, $d) =>
            $q->where('a.created_at', '<', Carbon::parse($d)->timezone('America/Fortaleza')->setTimezone('UTC')))
            ->when(!empty($filters['cru']), fn($q) => $q->whereIn('urc.id', $filters['cru']))
            ->select('urc.name as cru', DB::raw('count(distinct a.id) as total'))
            ->groupBy('urc.name')
            ->pluck('total', 'cru')
            ->toArray();

        $byURC = [];
        foreach ($groupedAttendances->keys() as $cru) {
            $respList = $groupedAttendances->get($cru);
            $totalRespCru = $respList->count();

            $sub = [];
            foreach ($scaleLabels as $k => $lbl) {
                $cnt = $respList->where('scale', $k)->count();
                $sub[] = [
                    'key'   => $lbl,
                    'value' => $totalRespCru > 0 ? round($cnt * 100 / $totalRespCru, 2) : 0,
                ];
            }

            $byURC[] = [
                'key'       => $cru,
                'value'     => $totalRespCru,
                'subGroups' => $sub,
            ];
        }

        $top10Bases = $responses
            ->groupBy('base')
            ->filter(fn($_, $name) => filled($name))
            ->map(function ($items, $name) use ($scaleLabels) {
                return [
                    'key' => $name,
                    'subGroups' => collect($scaleLabels)->map(function ($label, $scale) use ($items) {
                        return [
                            'key' => $label,
                            'value' => $items->where('scale', $scale)->count()
                        ];
                    })->values()->toArray()
                ];
            })
            ->sortByDesc(fn($item) => array_sum(array_column($item['subGroups'], 'value')))
            ->values()
            ->toArray();

        $top10Polos = $responses
            ->groupBy('polo')
            ->filter(fn($_, $name) => filled($name))
            ->map(function ($items, $name) use ($scaleLabels) {
                return [
                    'key' => $name,
                    'subGroups' => collect($scaleLabels)->map(function ($label, $scale) use ($items) {
                        return [
                            'key' => $label,
                            'value' => $items->where('scale', $scale)->count()
                        ];
                    })->values()->toArray()
                ];
            })
            ->sortByDesc(fn($item) => array_sum(array_column($item['subGroups'], 'value')))
            ->values()
            ->toArray();

        $top10Cities = $responses
            ->groupBy('city')
            ->filter(fn($_, $name) => filled($name))
            ->map(function ($items, $name) use ($scaleLabels) {
                return [
                    'key' => $name,
                    'subGroups' => collect($scaleLabels)->map(function ($label, $scale) use ($items) {
                        return [
                            'key' => $label,
                            'value' => $items->where('scale', $scale)->count()
                        ];
                    })->values()->toArray()
                ];
            })
            ->sortByDesc(fn($item) => array_sum(array_column($item['subGroups'], 'value')))
            ->values()
            ->toArray();


        $analysisServiceOffered = [
            'key' => '',
            'subGroups' => collect($scaleLabels)->map(function ($label, $scale) use ($responses) {
                return [
                    'key' => $label,
                    'value' => $responses->where('scale_service', $scale)->count()
                ];
            })->values()->toArray(),
        ];

        $analysisTelephone = [
            'key' => '',
            'subGroups' => collect($scaleLabels)->map(function ($label, $scale) use ($responses) {
                return [
                    'key' => $label,
                    'value' => $responses->where('scale_phone', $scale)->count()
                ];
            })->values()->toArray(),
        ];

        $analysisMecsTeam = [
            'key' => '',
            'subGroups' => collect($scaleLabels)->map(function ($label, $scale) use ($responses) {
                return [
                    'key' => $label,
                    'value' => $responses->where('scale_team', $scale)->count()
                ];
            })->values()->toArray(),
        ];

        $timeSpentPhoneLabels = [
            1 => 'Rápido',
            2 => 'Dentro do esperado',
            3 => 'Demorado',
            4 => 'Não fui eu quem fez a ligação para o telefone 192',
            5 => 'Não houve ligação para o telefone 192',
        ];

        $totalPhoneResponses = $responses->whereNotNull('time_spent_phone')->count();
        $analysisTimeSpentPhone = collect($timeSpentPhoneLabels)->map(function ($label, $value) use ($responses, $totalPhoneResponses) {
            $count = $responses->where('time_spent_phone', $value)->count();
            return [
                'key' => $label,
                'value' => $totalPhoneResponses > 0
                    ? round(($count * 100) / $totalPhoneResponses, 2)
                    : 0,
            ];
        })->values()->toArray();

        $timeAmbulanceLabels = [
            1 => 'Rápido',
            2 => 'Dentro do esperado',
            3 => 'Demorado',
            4 => 'Não estava no local quando a viatura chegou',
            5 => 'Não houve atendimento de emergência',
        ];

        $totalAmbulanceResponses = $responses->whereNotNull('time_ambulance_arrive')->count();
        $analysisTimeAmbulance = collect($timeAmbulanceLabels)->map(function ($label, $value) use ($responses, $totalAmbulanceResponses) {
            $count = $responses->where('time_ambulance_arrive', $value)->count();
            return [
                'key' => $label,
                'value' => $totalAmbulanceResponses > 0
                    ? round(($count * 100) / $totalAmbulanceResponses, 2)
                    : 0,
            ];
        })->values()->toArray();

        return response()->json([
            'totalResponses' => $totalResponses,
            'satisfiedCount' => [
                'total' => $totalEvaluatedAttendance,
                'veryDissatisfiedCount' => $veryDissatisfiedCount,
                'dissatisfiedCount' => $dissatisfiedCount,
                'neutralCount' => $neutralCount,
                'satisfiedCount' => $satisfiedCount,
                'verySatisfiedCount' => $verySatisfiedCount
            ],
            'satisfactionRate' => $satisfactionRate,
            'surveyResponseRate' => $surveyResponseRate,

            'avarageVeryDissatisfied' => [
                ['key' => 'Muito Insatisfeito', 'value' => $byCategory[4]['value'] ?? 0],
                ['key' => 'Geral', 'value' => 100 - ($byCategory[4]['value'] ?? 0)]
            ],
            'avarageDissatisfied' => [
                ['key' => 'Insatisfeito', 'value' => $byCategory[3]['value'] ?? 0],
                ['key' => 'Geral', 'value' => 100 - ($byCategory[3]['value'] ?? 0)]
            ],
            'avarageNeutral' => [
                ['key' => 'Neutro', 'value' => $byCategory[2]['value'] ?? 0],
                ['key' => 'Geral', 'value' => 100 - ($byCategory[2]['value'] ?? 0)]
            ],
            'avarageSatisfied' => [
                ['key' => 'Satisfeito', 'value' => $byCategory[1]['value'] ?? 0],
                ['key' => 'Geral', 'value' => 100 - ($byCategory[1]['value'] ?? 0)]
            ],
            'avarageVerySatisfied' => [
                ['key' => 'Muito Satisfeito', 'value' => $byCategory[0]['value'] ?? 0],
                ['key' => 'Geral', 'value' => 100 - ($byCategory[0]['value'] ?? 0)]
            ],
            'avarageGeneralSatisfaction' =>  $byCategory,

            'numberOfResponsesPerCRU' => collect($byURC)->map(fn($item) => [
                'key' => $item['key'],
                'subGroups' => [
                    ['key' => 'Total', 'value' => $item['value']]
                ]
            ]),
            'satisfactionRatePerCRU' => collect($byURC)->map(fn($item) => [
                'key' => $item['key'],
                'subGroups' => [
                    [
                        'key' => 'Satisfação',
                        'value' => $item['value'] > 0
                            ? round(collect($item['subGroups'])->whereIn('key', ['Satisfeito', 'Muito Satisfeito'])->sum('value'), 2)
                            : 0
                    ]
                ]
            ]),
            'surveyResponseRatePerCRU' => collect($byURC)->map(function ($item) use ($attendanceCounts) {
                $totalAttendances = $attendanceCounts[$item['key']] ?? 0;
                return [
                    'key' => $item['key'],
                    'subGroups' => [
                        [
                            'key' => 'Taxa de Resposta',
                            'value' => $totalAttendances > 0
                                ? round(($item['value'] * 100) / $totalAttendances, 2)
                                : 0
                        ]
                    ]
                ];
            }),
            'topBases' => $top10Bases,
            'topPoles' => $top10Polos,
            'topCities' => $top10Cities,
            'analysisServiceOffered' => [$analysisServiceOffered],
            'analysisTelephone' => [$analysisTelephone],
            'analysisMecsTeam' => [$analysisMecsTeam],
            'analysisTimeSpentPhone' => $analysisTimeSpentPhone,
            'analysisTimeAmbulanceArrive' => $analysisTimeAmbulance,
            'suggestions' => $suggestions,
        ]);
    }
}
