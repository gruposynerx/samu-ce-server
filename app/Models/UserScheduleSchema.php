<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @mixin IdeHelperUserScheduleSchema
 */
class UserScheduleSchema extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'schedulable',
        'valid_from',
        'valid_through',
        'days_of_week',
        'clock_in',
        'clock_out',
        'schedule_type_id',
    ];

    protected $casts = [
        'days_of_week' => 'array',
    ];

    public function schedulable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(UserSchedule::class, 'schema_id', 'id');
    }

    public function scheduleType(): BelongsTo
    {
        return $this->belongsTo(ScheduleType::class);
    }
}
