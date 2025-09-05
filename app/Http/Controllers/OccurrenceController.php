<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatusEnum;
use App\Http\Resources\LastOcurrencesPerProfessionalResource;
use App\Models\Attendance;
use App\Models\PrimaryAttendance;
use App\Models\SecondaryAttendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;

class OccurrenceController extends Controller
{
    protected string $primaryAttendanceSlug;

    protected string $secondaryAttendanceSlug;

    protected const CACHE_TTL = 900;

    public function __construct()
    {
        $this->primaryAttendanceSlug = app(PrimaryAttendance::class)->getMorphClass();
        $this->secondaryAttendanceSlug = app(SecondaryAttendance::class)->getMorphClass();
    }

    public function myLastAttendances(): ResourceCollection
    {
        $user = auth()->user();
        $userId = $user->id;
        $urcId = $user->urc_id;

        $endTime = now();
        $startTime = $endTime->copy()->subHours(12);

        $results = Attendance::select('attendances.*', DB::raw('MAX(user_attendances.created_at) as user_attendance_created_at'))
            ->join('tickets', 'attendances.ticket_id', '=', 'tickets.id')
            ->join('user_attendances', 'attendances.id', '=', 'user_attendances.attendance_id')
            ->where('user_attendances.user_id', $userId)
            ->where('attendances.urc_id', $urcId)
            ->whereIn('attendances.attendance_status_id', [AttendanceStatusEnum::COMPLETED->value, AttendanceStatusEnum::CANCELED->value])
            ->whereBetween('user_attendances.created_at', [$startTime, $endTime])
            ->groupBy('attendances.id', 'attendances.ticket_id', 'attendances.attendable_type', 'attendances.attendable_id', 'attendances.attendance_status_id', 'attendances.urc_id', 'attendances.created_at', 'attendances.updated_at', 'attendances.last_status_updated_at')
            ->with('patient', 'ticket')
            ->orderBy('user_attendance_created_at', 'desc')
            ->paginate(10);

        return LastOcurrencesPerProfessionalResource::collection($results);
    }

    public function myLastAttendancesShow(int $id): JsonResponse
    {
        $user = auth()->user();
        $userId = $user->id;
        $urcId = $user->urc_id;
        $attendance = Attendance::select('attendances.*', DB::raw('MAX(user_attendances.created_at) as user_attendance_created_at'))
            ->join('tickets', 'attendances.ticket_id', '=', 'tickets.id')
            ->join('user_attendances', 'attendances.id', '=', 'user_attendances.attendance_id')
            ->where('attendances.id', $id)
            ->where('user_attendances.user_id', $userId)
            ->where('attendances.urc_id', $urcId)
            ->whereIn('attendances.attendance_status_id', [AttendanceStatusEnum::COMPLETED->value, AttendanceStatusEnum::CANCELED->value])
            ->groupBy('attendances.id', 'attendances.ticket_id', 'attendances.attendable_type', 'attendances.attendable_id', 'attendances.attendance_status_id', 'attendances.urc_id', 'attendances.created_at', 'attendances.updated_at', 'attendances.last_status_updated_at')
            ->with('patient', 'ticket')
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'Attendance not found'], 404);
        }

        return response()->json(new LastOcurrencesPerProfessionalResource($attendance));
    }
}
