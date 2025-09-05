<?php

namespace App\Services;

use App\Enums\AttendanceStatusEnum;
use App\Enums\NotificationTypeEnum;
use App\Enums\RadioOperationFleetStatusEnum;
use App\Events\NotifyNoVehicleResponseEvent;
use App\Events\NotifyVehicleConfirmationEvent;
use App\Jobs\MarkFleetNoResponseJob;
use App\Jobs\RetryFleetNotification;
use App\Models\Attendance;
use App\Models\NotificationType;
use App\Models\RadioOperation;
use App\Models\RadioOperationFleet;
use App\Models\User;
use App\Scopes\UrcScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public const MAX_RETRIES = 1;

    public const RETRY_INTERVAL = 60;

    public function __construct(
        private readonly ExpoPushService $expoPushService,
    ) {}

    public function sendFleetNotification(RadioOperationFleet $fleet, string $action = 'awaiting_confirmation', int $retryCount = 0, bool $sentByApp = false)
    {
        if ($fleet->status === RadioOperationFleetStatusEnum::MANUAL_REGISTRATION_NO_APP->value) {
            return;
        }
        $notificationTypeName = $action === 'awaiting_confirmation' && $retryCount > 0
            ? NotificationTypeEnum::FLEET_ASSIGNMENT_REMINDER->value
            : NotificationTypeEnum::FLEET_ASSIGNMENT->value;

        $notificationType = NotificationType::where('name', $notificationTypeName)->first();
        if (!$notificationType) {
            return;
        }
        $fleetUsers = DB::table('radio_operation_fleet_user')
            ->where('radio_operation_fleet_id', $fleet->id)
            ->whereNotNull('user_id')
            ->get();

        if ($fleetUsers->isEmpty()) {
            return;
        }

        $userIds = $fleetUsers->pluck('user_id')->toArray();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        foreach ($fleetUsers as $fleetUser) {
            if (isset($users[$fleetUser->user_id])) {
                $this->scheduleNotification($users[$fleetUser->user_id], $fleet, $notificationType, $action, $retryCount);
            }
        }

        if ($retryCount === 0 && !$sentByApp) {
            for ($i = 1; $i <= self::MAX_RETRIES; $i++) {
                dispatch(new RetryFleetNotification($fleet->id, $notificationType->id, $action, $i))
                    ->onConnection('database')
                    ->delay(now()->addSeconds(self::RETRY_INTERVAL * $i));
            }

            dispatch(new MarkFleetNoResponseJob($fleet->id))
                ->onConnection('database')
                ->delay(now()->addSeconds(self::RETRY_INTERVAL * (self::MAX_RETRIES + 1)));
        }
    }

    public function scheduleNotification(User $user, RadioOperationFleet $fleet, NotificationType $notificationType, string $action, int $retryCount = 0)
    {
        $title = $this->makeTitle($action, $retryCount);
        $body = $this->makeBody($user->name, $action, $retryCount);
        $data = $this->makePayload($fleet, $notificationType, $action, $retryCount);

        try {
            $this->expoPushService->send($user->id, $title, $body, $data);
        } catch (\Exception $e) {
            Log::error('NotificationService::scheduleNotification - Erro ao enviar push', [
                'user_id' => $user->id,
                'fleet_id' => $fleet->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    public function markNotificationAsRead($userId, string $referenceId, string $referenceType): int
    {
        return DB::table('notifications')
            ->where('user_id', $userId)
            ->whereRaw("data->>'reference_id'   = ?", [$referenceId])
            ->whereRaw("data->>'reference_type' = ?", [$referenceType])
            ->whereNull('read_at')
            ->where('sent_at', '>=', now()->subSeconds(self::RETRY_INTERVAL))
            ->update(['read_at' => now()]);
    }

    public function markNotificationAsResponded($userId, string $referenceId, string $referenceType): int
    {
        $updated = DB::table('notifications')
            ->where('user_id', $userId)
            ->whereRaw("data->>'reference_id'   = ?", [$referenceId])
            ->whereRaw("data->>'reference_type' = ?", [$referenceType])
            ->whereNull('responded_at')
            ->where('sent_at', '>=', now()->subSeconds(self::RETRY_INTERVAL))
            ->update(['responded_at' => now()]);


        if ($referenceType === 'radio_operation_fleet' && $updated > 0) {

            $info = $this->getAttendanceInfoFromFleet($referenceId);

            if ($info) {
                NotifyVehicleConfirmationEvent::dispatch(
                    $info['attendance_id'],
                    $referenceId,
                    $info['creator_id'],
                    $info['number']
                );
            }
        }

        return $updated;
    }

    public function handleNoResponse(string $fleetId): void
    {
        DB::beginTransaction();
        try {
            $info = $this->getAttendanceInfoFromFleet($fleetId);

            if (!$info) {
                DB::rollBack();
                return;
            }

            $fleet = $info['fleet'];

            if ($fleet->status !== RadioOperationFleetStatusEnum::AWAITING_CONFIRMATION->value) {
                DB::rollBack();

                return;
            }

            $fleet->status = RadioOperationFleetStatusEnum::NO_RESPONSE->value;
            $fleet->save();

            Attendance::withoutGlobalScope(UrcScope::class)
                ->where('id', $info['attendance_id'])
                ->update([
                    'attendance_status_id' => AttendanceStatusEnum::NO_VEHICLE_RESPONSE->value,
                ]);

            DB::commit();

            NotifyNoVehicleResponseEvent::dispatch(
                $info['attendance_id'],
                $fleetId,
                $info['creator_id'],
                $info['number']
            );
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function makeTitle(string $action, int $retryCount): string
    {
        if ($action !== 'awaiting_confirmation') {
            return 'Nova ocorrência de emergência';
        }

        return $retryCount > 0
            ? 'LEMBRETE: Nova ocorrência aguardando confirmação'
            : 'Nova ocorrência aguardando confirmação';
    }

    private function makeBody(string $userName, string $action, int $retryCount): string
    {
        if ($action !== 'awaiting_confirmation') {
            return "{$userName}, foi designado para atender uma nova ocorrência.";
        }

        return $retryCount > 0
            ? "{$userName}, você ainda não confirmou a ocorrência designada."
            : "{$userName}, uma nova ocorrência está lhe aguardando.";
    }

    private function makePayload(RadioOperationFleet $fleet, $notificationType, string $action, int $retryCount): array
    {
        $ro = RadioOperation::withoutGlobalScope(UrcScope::class)
            ->findOrFail($fleet->radio_operation_id);

        $attendance = Attendance::withoutGlobalScope(UrcScope::class)
            ->findOrFail($ro->attendance_id);

        return [
            'attendance_id' => $attendance->id,
            'notification_type' => $notificationType->name,
            'notification_type_id' => $notificationType->id,
            'radio_operation_id' => $ro->id,
            'fleet_id' => $fleet->id,
            'action' => $action,
            'retry_count' => $retryCount,
            'reference_id' => $fleet->id,
            'reference_type' => 'radio_operation_fleet',
        ];
    }

    private function getAttendanceInfoFromFleet(string $fleetId): ?array
    {
        try {
            $fleet = RadioOperationFleet::findOrFail($fleetId);

            $radioOperation = RadioOperation::withoutGlobalScope(UrcScope::class)
                ->findOrFail($fleet->radio_operation_id);

            $attendanceId = $radioOperation->attendance_id;
            $creatorId = $radioOperation->created_by;

            $attendance = Attendance::withoutGlobalScope(UrcScope::class)
                ->findOrFail($attendanceId);

            $ticketData = DB::table('tickets')
                ->where('id', $attendance->ticket_id)
                ->first();

            $number = '';
            if ($ticketData) {
                $number = $ticketData->ticket_sequence_per_urgency_regulation_center . '/' . $attendance->attendance_sequence_per_ticket;
            } else {
                Log::warning('NotificationService::getAttendanceInfoFromFleet - Dados do ticket não encontrados', [
                    'fleet_id' => $fleetId,
                    'ticket_id' => $attendance->ticket_id,
                ]);
            }

            return
                [
                    'fleet' => $fleet,
                    'radio_operation' => $radioOperation,
                    'attendance' => $attendance,
                    'attendance_id' => $attendanceId,
                    'creator_id' => $creatorId,
                    'number' => $number,
                ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
