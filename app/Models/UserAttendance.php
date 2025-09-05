<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperUserAttendance
 */
class UserAttendance extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'attendance_id',
        'last_attendance_status_id',
        'new_attendance_status_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function lastAttendanceStatus(): BelongsTo
    {
        return $this->belongsTo(AttendanceStatus::class, 'id', 'last_attendance_status_id');
    }

    public function newAttendanceStatus(): BelongsTo
    {
        return $this->belongsTo(AttendanceStatus::class, 'id', 'new_attendance_status_id');
    }
}
