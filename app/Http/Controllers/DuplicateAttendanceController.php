<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatusEnum;
use App\Http\Requests\DuplicateAttendanceRequest;
use App\Models\Attendance;
use App\Models\MedicalRegulation;
use App\Models\Patient;
use App\Models\PrimaryAttendance;
use App\Models\SecondaryAttendance;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DuplicateAttendanceController extends Controller
{
    /**
     * POST api/attendance/duplicate
     *
     * Duplica um atendimento, se válido.
     */
    public function __invoke(DuplicateAttendanceRequest $request)
    {
        $data = $request->validated();

        $attendance = Attendance::findOrFail($data['attendance_id'])->load(['attendable', 'medicalRegulations', 'medicalRegulations.diagnosticHypotheses']);

        if (!in_array($attendance->attendable_type, [app(PrimaryAttendance::class)->getMorphClass(), app(SecondaryAttendance::class)->getMorphClass()])) {
            throw ValidationException::withMessages([
                'attendance_id' => 'O atendimento desse tipo não pode ser duplicado.',
            ]);
        }

        $attendanceStatus = $this->getDuplicateAttendanceStatus($attendance);

        DB::transaction(function () use ($attendance, $attendanceStatus) {
            $duplicatedAttendance = $attendance->replicate()->fill([
                'created_by' => auth()->user()->id,
                'attendance_status_id' => $attendanceStatus,
                'patient_id' => $attendance->patient ? Patient::create($attendance->patient->toArray())->id : null,
            ]);

            $attendable = app(Relation::getMorphedModel($attendance->attendable_type))
                ->create($attendance->attendable->toArray());

            $duplicatedAttendance->attendable_id = $attendable->id;
            $duplicatedAttendance->save();

            if ($attendanceStatus === AttendanceStatusEnum::AWAITING_VEHICLE_COMMITMENT->value) {
                $attendance->medicalRegulations->each(function (MedicalRegulation $regulation) use ($duplicatedAttendance) {

                    $duplicatedRegulation = $regulation->replicate();
                    $duplicatedRegulation->id = Str::orderedUuid()->toString();
                    $duplicatedRegulation->attendance_id = $duplicatedAttendance->id;
                    $duplicatedRegulation->action_details = $regulation->action_details;
                    $duplicatedRegulation->supporting_organizations = $regulation->supporting_organizations;
                    $duplicatedRegulation->created_at = $regulation->created_at;
                    $duplicatedRegulation->updated_at = $regulation->updated_at;
                    $duplicatedRegulation->diagnosticHypotheses()->sync(
                        collect($regulation->diagnosticHypotheses)
                            ->mapWithKeys(fn ($diagnosticHypothesis, $index) => [
                                $index => [
                                    'diagnostic_hypothesis_id' => $diagnosticHypothesis->pivot['diagnostic_hypothesis_id'],
                                    'nature_type_id' => $diagnosticHypothesis->pivot['nature_type_id'],
                                    'attendance_id' => $diagnosticHypothesis->pivot['attendance_id'],
                                    'created_by' => $diagnosticHypothesis->pivot['created_by'],
                                    'created_at' => $diagnosticHypothesis->pivot['created_at'],
                                    'updated_at' => $diagnosticHypothesis->pivot['updated_at'],
                                ],
                            ])
                            ->toArray()
                    );

                    $duplicatedRegulation->save();
                });
            }
        });

        return response()->json(['message' => 'Ocorrência duplicada com sucesso!']);
    }

    private function getDuplicateAttendanceStatus(Attendance $originalAttendance): int
    {
        $hasMedicalRegulations = $originalAttendance->medicalRegulations->count() > 0;

        if ($hasMedicalRegulations) {
            return AttendanceStatusEnum::AWAITING_VEHICLE_COMMITMENT->value;
        }

        return AttendanceStatusEnum::AWAITING_MEDICAL_REGULATION->value;
    }
}
