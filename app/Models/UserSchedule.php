<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperUserSchedule
 */
class UserSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'schema_id',
        'starts_at',
        'ends_at',
        'occupation_code',
        'observation',
        'prev_start_date',
        'prev_end_date',
        'urc_id',
        'base_id',
        'link',
        'shift_id',
        'position_jobs_id',
        'regional_group_id'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schema(): BelongsTo
    {
        return $this->belongsTo(UserScheduleSchema::class);
    }

    public function urc()
    {
        return $this->belongsTo(UrgencyRegulationCenter::class, 'urc_id');
    }

    public function base()
    {
        return $this->belongsTo(Base::class, 'base_id');
    }

    public function scheduleEvents()
    {
        return $this->hasMany(ScheduleEvent::class, 'user_schedule_id');
    }

    public function reverseScheduleEvents()
    {
        return $this->hasMany(ScheduleEvent::class, 'reverse_user_schedule_id');
    }
    
    public function userScheduleClocks()
    {
        return $this->hasMany(UserScheduleClocks::class);
    }

    public function positionJobs()
    {
        return $this->belongsTo(PositionJob::class, 'position_jobs_id');
    }

    public function regionalGroup()
    {
        return $this->belongsTo(RegionalGroup::class, 'regional_group_id');
    }
}
