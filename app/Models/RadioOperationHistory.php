<?php

namespace App\Models;

use App\Enums\RadioOperationEventTypeEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadioOperationHistory extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'radio_operation_id',
        'event_type',
        'event_timestamp',
        'sent_by_app',
        'created_by',
    ];

    protected $casts = [
        'event_type' => RadioOperationEventTypeEnum::class,
        'event_timestamp' => 'datetime',
        'sent_by_app' => 'boolean',
    ];

    public function radioOperation(): BelongsTo
    {
        return $this->belongsTo(RadioOperation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByEventType($query, RadioOperationEventTypeEnum $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeSentByApp($query, bool $sentByApp = true)
    {
        return $query->where('sent_by_app', $sentByApp);
    }

    public function scopeLatestForEventType($query, RadioOperationEventTypeEnum $eventType)
    {
        return $query->byEventType($eventType)
            ->orderBy('event_timestamp', 'desc')
            ->orderBy('created_at', 'desc');
    }

    public function getEventDisplayNameAttribute(): string
    {
        return $this->event_type->getDisplayName();
    }

    public function scopeBetweenDates($query, \Carbon\Carbon $startDate, \Carbon\Carbon $endDate)
    {
        return $query->whereBetween('event_timestamp', [$startDate, $endDate]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('event_timestamp', today());
    }

    public function wasCreatedByApp(): bool
    {
        return $this->sent_by_app;
    }

    public function wasCreatedManually(): bool
    {
        return !$this->sent_by_app;
    }
}
