<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\indexCalledConsultationRequest;

#[Group(name: 'Relatórios', description: 'Seção responsável pela gestão de relatórios')]
#[Subgroup(name: 'Consulta de chamados')]
class CallsConsultationController extends Controller
{
    public function indexListingCallByType(IndexCalledConsultationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $startDate = $data['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $data['end_date'] ?? now()->endOfMonth()->toDateString();
        $cities = $data['cities'] ?? [];
        $results = DB::table('tickets as t')
            ->selectRaw('count(*) as total, tt.name')
            ->join('ticket_types as tt', 'tt.id', '=', 't.ticket_type_id')
            ->join('attendances as a', 'a.ticket_id', '=', 't.id')
            ->where('t.urc_id', auth()->user()->urc_id)
            ->when(
                $startDate,
                fn($q) =>
                $q->whereRaw("(t.opening_at at time zone 'utc' at time zone 'America/Fortaleza') >= ?", [$startDate])
            )
            ->when(
                $endDate,
                fn($q) =>
                $q->whereRaw("(t.opening_at at time zone 'utc' at time zone 'America/Fortaleza') < ?", [$endDate])
            )
            ->when(
                !empty($cities),
                fn($q) =>
                $q->whereIn('t.city_id', $cities)
            )
            ->groupBy('tt.name')
            ->orderByDesc('total')
            ->get();

        $formatted = $results->map(fn($item) => [
            'key' => $item->name,
            'value' => (int) $item->total,
        ]);

        $grandTotal = $results->sum('total');
        return response()->json([
            'data' => $formatted,
            'grandTotal' => $grandTotal,
        ]);
    }
    public function indexDashboardCallAll(IndexCalledConsultationRequest $request): JsonResponse
    {
        $data = $request->validated();
        $startDate = $data['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $data['end_date'] ?? now()->endOfMonth()->toDateString();
        $cities = $data['cities'] ?? [];

        $days = (new \DateTime($endDate))->diff(new \DateTime($startDate))->days;
        $days = $days === 0 ? 1 : $days;

        $baseTicketQuery = fn() => DB::table('tickets as t')
            ->where('t.urc_id', auth()->user()->urc_id)
            ->whereRaw("(t.opening_at AT TIME ZONE 'utc' AT TIME ZONE 'America/Fortaleza') >= ?", [$startDate])
            ->whereRaw("(t.opening_at AT TIME ZONE 'utc' AT TIME ZONE 'America/Fortaleza') < ?", [$endDate])
            ->when(!empty($cities), fn($q) => $q->whereIn('t.city_id', $cities));

        $kpiQuery = (clone $baseTicketQuery())->join('attendances as a', 'a.ticket_id', '=', 't.id');
        $kpi = (clone $kpiQuery)
            ->selectRaw('COUNT(*) AS total_calls')
            ->selectRaw('SUM(CASE WHEN ticket_type_id = 3 THEN 1 ELSE 0 END) AS prank_calls')
            ->first();
        $kpiResult = [
            'totalCalls' => (int) $kpi->total_calls,
            'avarageCalls' => round($kpi->total_calls / $days, 2),
            'prankCalls' => (int) $kpi->prank_calls,
        ];

        $periodQuery = (clone $baseTicketQuery())
            ->join('ticket_types as tt', 'tt.id', '=', 't.ticket_type_id')
            ->join('attendances as a', 'a.ticket_id', '=', 't.id')
            ->selectRaw("
            DATE_TRUNC('month', t.opening_at AT TIME ZONE 'utc' AT TIME ZONE 'America/Fortaleza') AS month,
            tt.name AS type,
            COUNT(*) AS total
        ")
            ->groupBy('month', 'tt.name');

        $callsByPeriod = $periodQuery->get();

        $periodResult = $callsByPeriod
            ->groupBy('month')
            ->map(function ($group, $month) {
                $total = $group->sum('total');
                $subGroups = $group->map(fn($item) => [
                    'key' => $item->type,
                    'value' => (int) $item->total,
                ])
                    ->sortByDesc('value')
                    ->values()
                    ->toArray();
                return [
                    'key' => \Carbon\Carbon::parse($month)->format('Y/m'),
                    'value' => $total,
                    'subGroups' => $subGroups,
                ];
            })->values()->sortBy('key')->values()->toArray();



        $timeQuery = (clone $baseTicketQuery())
            ->join('ticket_types as tt', 'tt.id', '=', 't.ticket_type_id')
            ->join('attendances as a', 'a.ticket_id', '=', 't.id')
            ->selectRaw("
            TO_CHAR(t.opening_at AT TIME ZONE 'utc' AT TIME ZONE 'America/Fortaleza', 'HH24:00') AS time,
            tt.name AS type,
            COUNT(*) AS total_calls
        ")
            ->groupBy('time', 'tt.name');

        $callsByTime = $timeQuery->get();

        $timeResult = $callsByTime
            ->groupBy('time')
            ->map(function ($group, $time) use ($days) {
                $total = $group->sum('total_calls');
                $subGroups = $group->map(fn($item) => [
                    'key' => $item->type,
                    'value' => round($item->total_calls / $days, 2),
                ])
                    ->sortByDesc('value')
                    ->values()
                    ->toArray();
                return [
                    'key' => $time,
                    'value' => round($total / $days, 2),
                    'subGroups' => $subGroups,
                ];
            })->values()->sortBy('key')->values()->toArray();

        $professionalQuery = (clone $baseTicketQuery())
            ->join('users as u', 'u.id', '=', 't.created_by')
            ->join('ticket_types as tt', 'tt.id', '=', 't.ticket_type_id')
            ->join('attendances as a', 'a.ticket_id', '=', 't.id')
            ->selectRaw('u.name AS professional, tt.name AS type, COUNT(*) AS total')
            ->groupBy('u.name', 'tt.name');

        $callsByProfessional = $professionalQuery->get();

        $professionalResult = $callsByProfessional
            ->groupBy('professional')
            ->map(function ($group, $professional) {
                $total = $group->sum('total');
                $subGroups = $group->map(fn($item) => [
                    'key' => $item->type,
                    'value' => (int) $item->total,
                ])
                    ->sortByDesc('value')
                    ->values()
                    ->toArray();

                return [
                    'key' => $professional,
                    'value' => $total,
                    'subGroups' => $subGroups,
                ];
            })
            ->values()
            ->sortByDesc('value')
            ->values()
            ->toArray();

        return response()->json([
            'kpi' => $kpiResult,
            'callByPeriod' => $periodResult,
            'avarageCallsByTimeOfDay' => $timeResult,
            'calledByProfessionalAndType' => $professionalResult,
        ]);
    }
}
