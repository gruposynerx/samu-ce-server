<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperAttendanceTimeCount
 */
class AttendanceTimeCount extends Model
{
    use HasUuids;

    protected $fillable = [
        'attendance_id',
        'response_time',
        'response_time_measured_at',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
