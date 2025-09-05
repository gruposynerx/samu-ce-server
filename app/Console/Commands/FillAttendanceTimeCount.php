<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\AttendanceTimeCount;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FillAttendanceTimeCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fill-attendance-time-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inserts the average response time record for attendances that do not have';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $results = Attendance::withoutGlobalScopes()
            ->with([
                'radioOperation' => function ($query) {
                    $query->withoutGlobalScopes()->select('id', 'attendance_id', 'arrived_to_site_at');
                },
                'ticket' => function ($query) {
                    $query->withoutGlobalScopes()->select('id', 'opening_at');
                },
            ])
            ->whereHas('radioOperation', function ($query) {
                $query->withoutGlobalScopes()->whereNotNull('arrived_to_site_at');
            })
            ->whereHas('ticket', function ($query) {
                $query->withoutGlobalScopes()->whereBetween('opening_at', [Carbon::parse('2024-01-01')->startOfDay(), Carbon::parse('2024-02-14')->endOfDay()]);
            })
            ->orderBy('created_at')
            ->get();

        foreach ($results as $result) {
            AttendanceTimeCount::updateOrCreate(['attendance_id' => $result->id], [
                'attendance_id' => $result->id,
                'response_time_measured_at' => now(),
                'response_time' => Carbon::parse($result->radioOperation->arrived_to_site_at)->diffInSeconds($result->ticket->opening_at),
            ]);
        }
    }
}
