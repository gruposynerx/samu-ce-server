<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleEvent extends Model
{
    use HasFactory;
     protected $fillable = [
        'justification',
        'attachment',
        'user_schedule_id',
        'schedule_event_type_id',
        'reverse_professional_id',
        'professional_id',
        'reverse_user_schedule_id'
    ];

    public function reverseProfessional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reverse_professional_id');
    }
    
    public function scheduleEventType(): BelongsTo
    {
        return $this->belongsTo(ScheduleEventType::class, 'schedule_event_type_id');
    }

    public function userSchedule(): BelongsTo
    {
        return $this->belongsTo(UserSchedule::class, 'user_schedule_id');
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professional_id');
    }

    public function userScheduleReverseProfessional(): BelongsTo
    {
        return $this->belongsTo(UserSchedule::class, 'reverse_user_schedule_id');
    }
  
}
