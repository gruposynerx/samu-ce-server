<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelpeUserScheduleClocks
 */
class UserScheduleClocks extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_schedule_id',
        'user_id',
        'clock_in',
        'clock_out'
    ];

    public function userSchedule(): BelongsTo
    {
        return $this->belongsTo(UserSchedule::class, 'user_schedule_id');
    }
}
