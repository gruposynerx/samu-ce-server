<?php

namespace App\Models;

use App\Enums\RadioOperationEventTypeEnum;
use App\Traits\HasUrcId;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperRadioOperation
 */
class RadioOperation extends Model
{
    use HasUrcId, HasUuids, LogsActivity;

    protected $fillable = [
        'attendance_id',
        'vehicle_requested_at',
        'vehicle_dispatched_at',
        'vehicle_released_at',
        'arrived_to_site_at',
        'left_from_site_at',
        'arrived_to_destination_at',
        'release_from_destination_at',
        'urc_id',
        'vehicle_confirmed_at',
        'created_by',
    ];

    protected $casts = [
        'vehicle_requested_at' => 'datetime',
        'vehicle_dispatched_at' => 'datetime',
        'vehicle_released_at' => 'datetime',
        'arrived_to_site_at' => 'datetime',
        'left_from_site_at' => 'datetime',
        'arrived_to_destination_at' => 'datetime',
        'release_from_destination_at' => 'datetime',
        'vehicle_confirmed_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::created(function (RadioOperation $model) {
            if ($model->getAttribute('arrived_to_site_at')) {
                AttendanceTimeCount::updateOrCreate(['attendance_id' => $model->attendance_id], [
                    'attendance_id' => $model->attendance_id,
                    'response_time_measured_at' => now(),
                    'response_time' => Carbon::parse($model->arrived_to_site_at)->diffInSeconds($model->attendance->ticket->opening_at),
                ]);
            }
            $model->createHistoryForTimestamps();
        });

        static::updated(function (RadioOperation $model) {
            $model->createHistoryForChangedTimestamps();

            if (empty($model->attendance?->timeCount?->response_time) || $model->wasChanged('arrived_to_site_at')) {
                AttendanceTimeCount::updateOrCreate(['attendance_id' => $model->attendance_id], [
                    'attendance_id' => $model->attendance_id,
                    'response_time_measured_at' => now(),
                    'response_time' => Carbon::parse($model->arrived_to_site_at)->diffInSeconds($model->attendance->ticket->opening_at),
                ]);
            }
        });

        static::saving(function (RadioOperation $model) {
            if ($model->exists) {
                $model->createHistoryForChangedTimestamps();
            }
        });
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fleets(): HasMany
    {
        return $this->hasMany(RadioOperationFleet::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(RadioOperationNote::class);
    }

    public function vehicles(): HasManyThrough
    {
        return $this->hasManyThrough(
            Vehicle::class,
            RadioOperationFleet::class,
            'radio_operation_id',
            'id',
            'id',
            'vehicle_id'
        );
    }

    public function patrimonies(): HasManyThrough
    {
        return $this->hasManyThrough(
            Patrimony::class,
            RadioOperationNote::class,
            'radio_operation_id',
            'id',
            'id',
            'patrimony_id'
        );
    }

    public function fleetHistories(): HasMany
    {
        return $this->hasMany(RadioOperationFleetHistory::class);
    }

    public function previousFleet(): HasOne
    {
        return $this->hasOne(RadioOperationFleetHistory::class)->latest();
    }

    public function histories(): HasMany
    {
        return $this->hasMany(RadioOperationHistory::class);
    }

    public function updateTimestamp(
        RadioOperationEventTypeEnum $eventType,
        Carbon $timestamp,
        bool $sentByApp = false,
        string $createdBy = null
    ): void {
        $this->update([
            $eventType->value => $timestamp,
        ]);

        $this->histories()->create([
            'event_type' => $eventType,
            'event_timestamp' => $timestamp,
            'sent_by_app' => $sentByApp,
            'created_by' => $createdBy,
        ]);
    }

    public function getLatestTimestampFromHistory(RadioOperationEventTypeEnum $eventType): ?Carbon
    {
        $latestHistory = $this->histories()
            ->latestForEventType($eventType)
            ->first();

        return $latestHistory?->event_timestamp;
    }

    public function wasLatestEventSentByApp(RadioOperationEventTypeEnum $eventType): bool
    {
        $latestHistory = $this->histories()
            ->latestForEventType($eventType)
            ->first();

        return $latestHistory?->sent_by_app ?? false;
    }

    public function getEventHistory(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->histories()
            ->with('creator')
            ->orderBy('event_timestamp')
            ->orderBy('created_at')
            ->get();
    }

    public function createHistoryForTimestamps(): void
    {
        foreach (RadioOperationEventTypeEnum::cases() as $eventType) {
            $timestampValue = $this->getAttribute($eventType->value);
            if ($timestampValue) {
                $sentByApp = request()->boolean('sent_by_app', false);
                $existingHistory = $this->histories()
                    ->where('event_type', $eventType)
                    ->where('event_timestamp', $timestampValue)
                    ->where('sent_by_app', $sentByApp)
                    ->exists();

                if (!$existingHistory) {
                    $this->histories()->create([
                        'event_type' => $eventType,
                        'event_timestamp' => $timestampValue,
                        'sent_by_app' => $sentByApp,
                        'created_by' => auth()->id(),
                    ]);
                }
            }
        }
    }

    public function createHistoryForChangedTimestamps(): void
    {
        $requestData = request()->all();
        $sentByApp = request()->boolean('sent_by_app', false);

        foreach (RadioOperationEventTypeEnum::cases() as $eventType) {
            $fieldName = $eventType->value;
            $fieldValue = $this->getAttribute($fieldName);
            $wasChanged = $this->wasChanged($fieldName);

            $fieldSentInRequest = array_key_exists($fieldName, $requestData);

            $shouldCreateHistory = false;

            if ($fieldValue && $fieldSentInRequest) {
                $shouldCreateHistory = true;
            }

            if ($shouldCreateHistory) {
                if ($sentByApp) {
                    $existingHistory = $this->histories()
                        ->where('event_type', $eventType)
                        ->where('event_timestamp', $fieldValue)
                        ->where('sent_by_app', true)
                        ->exists();

                    if ($existingHistory) {
                        continue;
                    }
                }

                $historyRecord = $this->histories()->create([
                    'event_type' => $eventType,
                    'event_timestamp' => $fieldValue,
                    'sent_by_app' => $sentByApp,
                    'created_by' => auth()->id(),
                ]);

            }
        }
        $this->syncTimestampsWithLatestHistory();
    }

    public function syncTimestampsWithLatestHistory(): void
    {
        $updates = [];

        foreach (RadioOperationEventTypeEnum::cases() as $eventType) {
            $latestTimestamp = $this->getLatestTimestampFromHistory($eventType);
            $currentTimestamp = $this->getAttribute($eventType->value);
            if ($latestTimestamp && (!$currentTimestamp || $latestTimestamp->ne($currentTimestamp))) {
                $updates[$eventType->value] = $latestTimestamp;
            }
        }

        if (!empty($updates)) {
            $this->updateQuietly($updates);
        }
    }

    public function syncTimestampsFromHistory(): void
    {
        foreach (RadioOperationEventTypeEnum::cases() as $eventType) {
            $latestTimestamp = $this->getLatestTimestampFromHistory($eventType);
            if ($latestTimestamp) {
                $this->update([
                    $eventType->value => $latestTimestamp,
                ]);
            }
        }
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable();
    }
}
