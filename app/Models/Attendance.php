<?php

namespace App\Models;

use App\Actions\SendWhatsAppMessage;
use App\Enums\AttendanceStatusEnum;
use App\Events\ChangeAttendance;
use App\Events\RefreshAttendance\RefreshPrimaryAttendance;
use App\Events\RefreshAttendance\RefreshRadioOperation;
use App\Events\RefreshAttendance\RefreshSecondaryAttendance;
use App\Observers\UrcObserver;
use App\Scopes\UrcScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

/**
 * @mixin IdeHelperAttendance
 */
class Attendance extends Model
{
    use HasRelationships, HasUuids;

    protected $fillable = [
        'attendable',
        'ticket_id',
        'urc_id',
        'created_by',
        'patient_id',
        'attendance_sequence_per_ticket',
        'attendance_status_id',
        'last_status_updated_at',
        'is_late_occurrence',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::observe(new UrcObserver());
        static::addGlobalScope(new UrcScope());

        static::creating(function ($model) {
            $last = $model->where('ticket_id', $model->ticket_id)->max('attendance_sequence_per_ticket');
            $model->attendance_sequence_per_ticket = $last + 1;

            if ($model->isDirty('attendance_status_id')) {
                $model->last_status_updated_at = now();
            }
        });

        static::created(function (Attendance $model) {
            $attendanceType = $model->attendable_type;
            $action = 'created';

            match ($attendanceType) {
                app(SecondaryAttendance::class)->getMorphClass() => [
                    RefreshSecondaryAttendance::dispatch($model->attendable, $action),
                    RefreshRadioOperation::dispatch(null, $action),
                ],
                default => null,
            };

            $monitoringData = [
                'attendance_requested_at' => $model->ticket->opening_at,
                'ticket_id' => $model->ticket_id,
            ];

            $model->monitoring()->create($monitoringData);

            $model->logs()->create([
                'user_id' => auth()->user()->id,
                'current_attendance_status_id' => $model->attendance_status_id,
                'previous_attendance_status_id' => null,
            ]);

            $model->userAttendances()->create([
                'user_id' => auth()->user()->id,
                'last_attendance_status_id' => null,
                'new_attendance_status_id' => $model->attendance_status_id,
            ]);
        });

        static::updated(function (Attendance $model) {
            $attendanceType = $model->attendable_type;

            $action = 'updated';

            match ($attendanceType) {
                app(PrimaryAttendance::class)->getMorphClass() => [
                    RefreshPrimaryAttendance::dispatch($model->attendable, $action),
                    RefreshRadioOperation::dispatch($model->radioOperation, $action),
                    ChangeAttendance::dispatch($model->ticket_id, $model->id),
                ],
                app(SecondaryAttendance::class)->getMorphClass() => [
                    RefreshSecondaryAttendance::dispatch($model->attendable, $action),
                    RefreshRadioOperation::dispatch($model->radioOperation, $action),
                ],
                default => null,
            };

            if ($model->isDirty('attendance_status_id')) {
                // SendWhatsAppMessage::run($model);

                $model->logs()->create([
                    'user_id' => auth()->user()->id,
                    'current_attendance_status_id' => $model->attendance_status_id,
                    'previous_attendance_status_id' => $model->getOriginal('attendance_status_id'),
                ]);
            }

            $model->load('latestLog', 'sceneRecording');

            $monitoringData = [
                'attendance_requested_at' => $model->ticket->opening_at,
                'vehicle_dispatched_at' => $model->radioOperation?->vehicle_dispatched_at,
                'in_attendance_at' => $model->sceneRecording?->created_at,
            ];

            $model->monitoring()->update($monitoringData);

            $lastStatusIsCompleted = $model->latestLog->current_attendance_status_id === AttendanceStatusEnum::COMPLETED->value;
            $lastStatusIsCanceled = $model->latestLog->current_attendance_status_id === AttendanceStatusEnum::CANCELED->value;

            if ($lastStatusIsCompleted || $lastStatusIsCanceled) {
                $model->monitoring()->update([
                    'attendance_completed_at' => $model->last_status_updated_at,
                    'canceled' => $lastStatusIsCanceled,
                ]);
            }
        });

        static::updating(function (Attendance $model) {
            if ($model->isDirty('attendance_status_id')) {
                $model->last_status_updated_at = now();
            }
        });
    }

    protected $appends = [
        'number',
    ];

    public function number(): Attribute
    {
        if (!$this->relationLoaded('ticket')) {
            return Attribute::get(fn () => '');
        }

        return Attribute::make(get: fn () => "{$this->ticket->ticket_sequence_per_urgency_regulation_center}/$this->attendance_sequence_per_ticket");
    }

    public function attendable(): MorphTo
    {
        return $this->morphTo();
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function urgencyRegulationCenter(): BelongsTo
    {
        return $this->belongsTo(UrgencyRegulationCenter::class, 'urc_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(AttendanceStatus::class);
    }

    public function medicalRegulations(): HasMany
    {
        return $this->hasMany(MedicalRegulation::class, 'attendance_id', 'id');
    }

    public function userAttendances(): HasMany
    {
        return $this->hasMany(UserAttendance::class, 'attendance_id', 'id');
    }

    public function latestUserAttendance(): HasOne
    {
        return $this->hasOne(UserAttendance::class, 'attendance_id', 'id')->latest();
    }

    public function sceneRecording(): HasOne
    {
        return $this->hasOne(SceneRecording::class, 'attendance_id', 'id')->latest();
    }

    public function sceneRecordings(): HasMany
    {
        return $this->hasMany(SceneRecording::class, 'attendance_id', 'id');
    }

    public function latestMedicalRegulation(): HasOne
    {
        return $this->hasOne(MedicalRegulation::class, 'attendance_id', 'id')->latest();
    }

    public function firstMedicalRegulation(): HasOne
    {
        return $this->hasOne(MedicalRegulation::class, 'attendance_id', 'id')->oldest();
    }

    public function radioOperation(): HasOne
    {
        return $this->hasOne(RadioOperation::class);
    }

    public function observations(): HasMany
    {
        return $this->hasMany(AttendanceObservation::class);
    }

    public function evolutions(): HasMany
    {
        return $this->hasMany(AttendanceEvolution::class);
    }

    public function latestLog(): HasOne
    {
        return $this->hasOne(AttendanceLog::class)->latestOfMany();
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function timeCount(): HasOne
    {
        return $this->hasOne(AttendanceTimeCount::class);
    }

    public function cancellation()
    {
        return $this->hasOne(AttendanceCancellationRecord::class);
    }

    public function latestVehicleStatusHistory(): HasOne
    {
        return $this->hasOne(
            VehicleStatusHistory::class,
            'attendance_id',
        )->latestOfMany();
    }

    public function vehicleStatusHistories(): HasMany
    {
        return $this->hasMany(VehicleStatusHistory::class, 'attendance_id');
    }

    public function childrenLinks(): HasManyThrough
    {
        return $this->hasManyThrough(
            Attendance::class,
            AttendanceLink::class,
            'father_link_id',
            'id',
            'id',
            'children_link_id',
        );
    }

    public function fatherLink(): HasOneThrough
    {
        return $this->hasOneThrough(
            Attendance::class,
            AttendanceLink::class,
            'children_link_id',
            'id',
            'id',
            'father_link_id',
        );
    }

    public function monitoring(): HasOne
    {
        return $this->hasOne(AttendanceMonitoring::class);
    }

    public function requesterSatisfaction(): HasOne
    {
        return $this->hasOne(RequesterSatisfaction::class);
    }
}
