<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperAttendanceObservation
 */
class AttendanceObservation extends Model
{
    use HasUuids;

    protected $fillable = [
        'attendance_id',
        'created_by',
        'role_creator_id',
        'observation',
        'created_at',
        'sent_by_app',
    ];

    public static function boot(): void
    {
        parent::boot();

        self::creating(static function ($model) {
            $model->role_creator_id = auth()->user()->current_role;
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

    public function roleCreator(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_creator_id');
    }
}
