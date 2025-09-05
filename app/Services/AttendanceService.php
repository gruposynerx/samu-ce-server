<?php

namespace App\Services;

use App\Enums\AttendanceStatusEnum;
use App\Enums\PlaceStatusEnum;
use App\Enums\RadioOperationFleetStatusEnum;
use App\Enums\VehicleStatusEnum;
use App\Exceptions\AttendanceException;
use App\Models\Attendance;
use App\Models\RadioOperation;
use App\Models\Vehicle;
use App\Models\VehicleStatusHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class AttendanceService
{
    public function __construct(
        protected Attendance $attendanceRepository,
        protected Vehicle $vehicleRepository
    ) {
    }

    private function sanitizeParams(string|Attendance $attendance, string|RadioOperation $radioOperation = null): array
    {
        $return = [];

        $return['attendance'] = is_string($attendance) ? $this->attendanceRepository->find($attendance) : $attendance;

        if ($radioOperation) {
            $return['radioOperation'] = is_string($radioOperation) ? $attendance->radioOperation()->find($radioOperation) : $radioOperation;
        }

        return $return;
    }

    /**
     * @throws AttendanceException
     */
    public function alreadyFinished(int $statusId): void
    {
        if ($statusId === AttendanceStatusEnum::COMPLETED->value) {
            throw AttendanceException::alreadyFinished();
        }
    }

    /**
     * @throws AttendanceException
     */
    public function start(string|Attendance $attendanceId, int $nextStatusId, bool $forceStart = false): Attendance
    {
        $attendance = is_string($attendanceId) ? $this->attendanceRepository->find($attendanceId) : $attendanceId;

        $allInAttendanceStatus = array_column(AttendanceStatusEnum::ALL_STATUSES_IN_ATTENDANCE, 'value');

        $lastAttendanceStatus = $attendance->attendance_status_id;

        if ($lastAttendanceStatus === AttendanceStatusEnum::COMPLETED->value && !$forceStart) {
            throw AttendanceException::alreadyFinished();
        }

        $isAlreadyInAttendance = in_array($lastAttendanceStatus, $allInAttendanceStatus, true);

        $responsible = $attendance->userAttendances()->latest()->first()?->load('user')->user;
        $isNotResponsible = $responsible?->id !== auth()->id();

        if ($isAlreadyInAttendance && $isNotResponsible) {
            $data = [
                'responsible' => $responsible->name ?? '',
                'occurrence_number' => "{$attendance->ticket->ticket_sequence_per_urgency_regulation_center}/{$attendance->attendance_sequence_per_ticket}",
            ];

            throw AttendanceException::alreadyInAttendance($data['responsible'], $data['occurrence_number']);
        }

        if (!$isAlreadyInAttendance) {
            $attendance->update([
                'attendance_status_id' => $nextStatusId,
            ]);

            $attendance->userAttendances()->create([
                'user_id' => auth()->user()->id,
                'last_attendance_status_id' => $lastAttendanceStatus,
                'new_attendance_status_id' => $attendance->attendance_status_id,
            ]);
        }

        return $attendance;
    }

    public function check(string|Attendance $attendanceId, int $statusId): JsonResponse
    {
        $attendance = is_string($attendanceId) ? $this->attendanceRepository->findOrFail($attendanceId) : $attendanceId;

        $response = [
            'ticket_type_id' => $attendance->ticket->ticket_type_id,
            'attendance_status_id' => $attendance->attendance_status_id,
        ];

        if ($attendance->attendance_status_id !== $statusId || $attendance->userAttendances()->latest()->first()?->user_id !== auth()->user()->id) {
            return response()->json($response, Response::HTTP_CONFLICT);
        }

        return response()->json($response, Response::HTTP_OK);
    }

    private function updateAttendanceAndVehicleStatuses(
        Attendance $attendance,
        AttendanceStatusEnum $attendanceStatus,
        array $vehicles,
        VehicleStatusEnum $vehicleStatus
    ): Attendance {
        $attendance->update([
            'attendance_status_id' => $attendanceStatus,
        ]);

        foreach ($vehicles as $vehicleId) {
            VehicleStatusHistory::create([
                'vehicle_id' => $vehicleId,
                'vehicle_status_id' => $vehicleStatus,
                'attendance_id' => $attendance->id,
            ]);
        }

        return $attendance;
    }

    public function changeToAwaitingVehicle(string|Attendance $attendance, string|RadioOperation $radioOperation, string $eventStatus): Attendance
    {
        ['attendance' => $attendance, 'radioOperation' => $radioOperation] = $this->sanitizeParams($attendance, $radioOperation);

        $radioOperationFleets = $radioOperation->fleets();

        if (!$radioOperation->attendance->sceneRecording()->exists()) {
            $radioOperationFleets->update([
                'status' => RadioOperationFleetStatusEnum::AWAITING_CONFIRMATION,
            ]);
        }

        return $this->updateAttendanceAndVehicleStatuses(
            $attendance,
            AttendanceStatusEnum::AWAITING_VEHICLE_COMMITMENT,
            $radioOperationFleets->pluck('vehicle_id')->toArray(),
            VehicleStatusEnum::SOLICITED
        );
    }

    public function changeToVehicleDispatched(string|Attendance $attendance, string|RadioOperation $radioOperation, string $eventStatus): Attendance
    {
        ['attendance' => $attendance, 'radioOperation' => $radioOperation] = $this->sanitizeParams($attendance, $radioOperation);

        return $this->updateAttendanceAndVehicleStatuses(
            $attendance,
            AttendanceStatusEnum::VEHICLE_SENT,
            $radioOperation->fleets()->pluck('vehicle_id')->toArray(),
            VehicleStatusEnum::COMMITTED
        );
    }

    public function changeToVehicleReleased(string|Attendance $attendance, string|RadioOperation $radioOperation, string $eventStatus): Attendance
    {
        ['attendance' => $attendance, 'radioOperation' => $radioOperation] = $this->sanitizeParams($attendance, $radioOperation);

        $radioOperation->fleets()->update(['finished' => true]);

        return $this->updateAttendanceAndVehicleStatuses(
            $attendance,
            AttendanceStatusEnum::COMPLETED,
            $radioOperation->fleets()->pluck('vehicle_id')->toArray(),
            VehicleStatusEnum::ACTIVE
        );
    }

    public function changeToCancelled(string|Attendance $attendance): Attendance
    {
        ['attendance' => $attendance] = $this->sanitizeParams($attendance);

        if ($attendance->radioOperation) {
            $radioOperation = $attendance->radioOperation;

            $latestVehicleStatusHistoryQuery = $attendance->radioOperation()->with('vehicles.latestVehicleStatusHistory');

            $hasCommittedVehicles = $latestVehicleStatusHistoryQuery->whereHas('vehicles.latestVehicleStatusHistory', function ($history) {
                $history->where('vehicle_status_id', VehicleStatusEnum::COMMITTED);
            })->exists();

            $awaitingConfirmationVehicles = $latestVehicleStatusHistoryQuery->whereHas('vehicles.latestVehicleStatusHistory', function ($history) {
                $history->where('vehicle_status_id', VehicleStatusEnum::SOLICITED);
            })->get();

            if ($radioOperation->vehicle_dispatched_at && $hasCommittedVehicles) {
                throw ValidationException::withMessages([
                    'vehicle' => 'Ocorrência com viatura empenhada. Informe a liberação da viatura',
                ]);
            }

            if ($radioOperation->vehicle_requested_at && $awaitingConfirmationVehicles->count()) {
                $this->updateAttendanceAndVehicleStatuses(
                    $attendance,
                    AttendanceStatusEnum::CANCELED,
                    $awaitingConfirmationVehicles->pluck('vehicles')->flatten()->pluck('id')->toArray(),
                    VehicleStatusEnum::ACTIVE
                );

                return $attendance;
            }
        }

        $attendance->update(['attendance_status_id' => AttendanceStatusEnum::CANCELED->value]);

        return $attendance;
    }

    public function changeToAwaitingConduct(string|Attendance $attendance): Attendance
    {
        ['attendance' => $attendance] = $this->sanitizeParams($attendance);

        $latestVehicleStatusHistoryQuery = $attendance->radioOperation()->with('vehicles.latestVehicleStatusHistory');

        $histories = $latestVehicleStatusHistoryQuery->whereHas('vehicles')->get();

        if ($histories->count()) {
            return $this->updateAttendanceAndVehicleStatuses(
                $attendance,
                AttendanceStatusEnum::AWAITING_CONDUCT,
                $histories->pluck('vehicles')->flatten()->pluck('id')->toArray(),
                VehicleStatusEnum::COMMITTED,
            );
        }

        $attendance->update(['attendance_status_id' => AttendanceStatusEnum::AWAITING_CONDUCT->value]);

        return $attendance;
    }

    public function changeToConduct(string|Attendance $attendance): Attendance
    {
        ['attendance' => $attendance] = $this->sanitizeParams($attendance);

        $attendance->update(['attendance_status_id' => AttendanceStatusEnum::CONDUCT->value]);

        return $attendance;
    }

    public function abandonAttendancesInProgress($user = null): void
    {
        $user = $user ?? auth()->user();

        if ($user?->attendancesInProgress()->exists()) {
            foreach ($user?->attendancesInProgress as $attendance) {
                $previousStatus = $attendance->latestLog->previous_attendance_status_id;

                $attendance->update(['attendance_status_id' => $previousStatus]);
            }
        }
    }

    public function abandonOccupiedPlace(): void
    {
        $user = auth()->user();

        if ($user?->place) {
            $user->place->update([
                'user_id' => null,
                'place_status_id' => PlaceStatusEnum::FREE->value,
            ]);
        }
    }
}
