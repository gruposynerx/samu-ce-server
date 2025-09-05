<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperAttendanceCancellationRecord
 */
class AttendanceCancellationRecord extends Model
{
    use HasUuids;

    protected $fillable = [
        'attendance_id',
        'created_by',
        'requester',
        'reason',
    ];

    public static function boot(): void
    {
        parent::boot();

        self::creating(static function ($model) {
            $model->created_by = auth()->user()->id;
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
}
