<?php

namespace App\Console;

use App\Models\PersonalAccessToken;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {

            $idleTokens = PersonalAccessToken::whereHas('tokenable', function ($query) {
                $query->where('last_seen', '<', now()->subMinutes(config('auth.logout_after_inactivity_time')))
                    ->whereHas('attendancesInProgress');
            })->get();

            $idleTokens->each(function ($idleToken) {
                foreach ($idleToken->tokenable->attendancesInProgress as $attendance) {
                    $previousStatus = $attendance->latestLog->previous_attendance_status_id;

                    $attendance->update(['attendance_status_id' => $previousStatus]);
                }
            });

            $idleTokens->delete();

        })->everyFiveMinutes();

        $schedule->command('sanctum:prune-expired --hours=24')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
