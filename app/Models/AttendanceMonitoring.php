<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperAttendanceMonitoring
 */
class AttendanceMonitoring extends Model
{
    use HasUuids;

    protected $fillable = [
        'attendance_id',
        'attendance_requested_at',
        'vehicle_dispatched_at',
        'in_attendance_at',
        'attendance_completed_at',
    ];

    protected $casts = [
        'attendance_requested_at' => 'datetime',
        'vehicle_dispatched_at' => 'datetime',
        'in_attendance_at' => 'datetime',
        'attendance_completed_at' => 'datetime',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
