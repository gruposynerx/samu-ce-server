<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperRequesterSatisfaction
 */
class RequesterSatisfaction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'attendance_id',
        'requester_id',
        'requester_sugestion',
        'scale_satisfaction_service_offered',
        'scale_attendance_provided_mecs_team',
        'scale_telephone_attendance',
        'satisfaction_time_ambulance_arrive_id',
        'satisfaction_time_spent_phone_id',
    ];

    protected $table = 'requester_satisfactions';

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Requester::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
