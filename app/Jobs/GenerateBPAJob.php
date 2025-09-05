<?php

namespace App\Jobs;

use App\Enums\AttendanceStatusEnum;
use App\Enums\BPAReportStatusEnum;
use App\Enums\VehicleTypeEnum;
use App\Events\BPAGeneratedEvent;
use App\Models\Attendance;
use App\Models\BPAReport;
use App\Models\Ticket;
use App\Scopes\UrcScope;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateBPAJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly array $period, private readonly string $urcId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $period = $this->period;
        $urcId = $this->urcId;

        $key = $urcId . $period['start']->format('Ymd') . $period['end']->format('Ymd');

        $period = [$period['start'], $period['end']];

        $totalTickets = Ticket::withoutGlobalScope(UrcScope::class)
            ->where('urc_id', $urcId)
            ->whereBetween('opening_at', $period)
            ->count();

        $totalTicketsWithMedicalOrientation = Ticket::withoutGlobalScope(UrcScope::class)
            ->where('urc_id', $urcId)
            ->whereBetween('opening_at', $period)
            ->whereHas('attendances', function (Builder $query) {
                $query->withoutGlobalScope(UrcScope::class)
                    ->whereHas('latestMedicalRegulation', fn (Builder $q) => $q->withoutGlobalScope(UrcScope::class)->whereJsonContains('action_details', 'Orientação'));
            })
            ->count();

        $totalAttendancesWithBasicSupportUnits = Attendance::withoutGlobalScope(UrcScope::class)
            ->where('urc_id', $urcId)
            ->with(['ticket' => fn ($q) => $q->withoutGlobalScope(UrcScope::class)->select(['id', 'ticket_sequence_per_urgency_regulation_center'])])
            ->whereHas('ticket', fn (Builder $q) => $q->withoutGlobalScope(UrcScope::class)->whereBetween('opening_at', $period))
            ->whereHas('latestVehicleStatusHistory', function (Builder $query) {
                $query->withoutGlobalScope(UrcScope::class)
                    ->whereIn('vehicle_type_id', [VehicleTypeEnum::BASIC_SUPPORT_UNIT, VehicleTypeEnum::MOTORCYCLE_AMBULANCE]);
            })
            ->count();

        $totalAttendancesWithAdvancedSupportUnits = Attendance::withoutGlobalScope(UrcScope::class)
            ->where('urc_id', $urcId)
            ->with(['ticket' => fn ($q) => $q->withoutGlobalScope(UrcScope::class)->select(['id', 'ticket_sequence_per_urgency_regulation_center'])])
            ->whereHas('ticket', fn (Builder $q) => $q->withoutGlobalScope(UrcScope::class)->whereBetween('opening_at', $period))
            ->whereHas('latestVehicleStatusHistory', function (Builder $query) {
                $query->withoutGlobalScope(UrcScope::class)
                    ->whereIn('vehicle_type_id', [VehicleTypeEnum::ADVANCED_SUPPORT_UNIT, VehicleTypeEnum::AEROMEDICAL]);
            })
            ->count();

        $totalTicketsWithMultipleAttendances = Ticket::withoutGlobalScope(UrcScope::class)
            ->where('urc_id', $urcId)
            ->whereBetween('opening_at', $period)
            ->where(\DB::raw('(select count("id") from "attendances" where "tickets"."id" = "attendances"."ticket_id")'), '>', 1)
            ->count();

        $totalAttendancesPerBaseAndType = DB::table('attendances')
            ->select([
                'bases.name',
                'bases.national_health_registration',
                'vehicle_status_histories.vehicle_type_id',
                DB::raw("SUM(case when attendances.attendable_type = 'primary_attendance' then 1 end) as primary_attendance_count"),
                DB::raw("SUM(case when attendances.attendable_type = 'secondary_attendance' then 1 end) as secondary_attendance_count"),
                DB::raw('COUNT(*) as total_attendance_count'),
            ])
            ->join('tickets', 'tickets.id', '=', 'attendances.ticket_id')
            ->join('radio_operations', 'radio_operations.attendance_id', '=', 'attendances.id')
            ->join('vehicle_status_histories', 'vehicle_status_histories.id', '=', DB::raw('(SELECT id FROM vehicle_status_histories WHERE vehicle_status_histories.attendance_id = attendances.id AND vehicle_status_histories.vehicle_status_id = 1 ORDER BY vehicle_status_histories.created_at DESC LIMIT 1)'))
            ->join('bases', 'bases.id', '=', 'vehicle_status_histories.base_id')
            ->whereBetween('tickets.opening_at', $period)
            ->where('attendances.urc_id', $urcId)
            ->where('attendances.attendance_status_id', AttendanceStatusEnum::COMPLETED)
            ->whereNotNull('radio_operations.arrived_to_site_at')
            ->groupBy('bases.name', 'bases.national_health_registration', 'vehicle_status_histories.vehicle_type_id')
            ->get()->mapToGroups(function ($history) {
                return [
                    Str::lower(VehicleTypeEnum::tryFrom($history->vehicle_type_id)->name) => $history,
                ];
            })->map(function ($history) {
                return [
                    'data' => $history,
                    'primary' => $history->sum('primary_attendance_count'),
                    'secondary' => $history->sum('secondary_attendance_count'),
                    'total' => $history->sum('total_attendance_count'),
                ];
            });

        $data = [
            'total_tickets' => $totalTickets,
            'total_tickets_with_medical_orientation' => $totalTicketsWithMedicalOrientation,
            'total_attendances_with_basic_support_units' => $totalAttendancesWithBasicSupportUnits,
            'total_attendances_with_advanced_support_units' => $totalAttendancesWithAdvancedSupportUnits,
            'total_tickets_with_multiple_attendances' => $totalTicketsWithMultipleAttendances,
            'total_attendances_per_base_and_type' => $totalAttendancesPerBaseAndType,
        ];

        $report = BPAReport::where('key', $key)->first();
        $report->update(['data' => $data, 'status' => BPAReportStatusEnum::COMPLETE]);
        $report->refresh();

        BPAGeneratedEvent::dispatch($report->key);
    }
}
