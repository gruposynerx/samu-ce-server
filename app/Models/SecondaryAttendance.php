<?php

namespace App\Models;

use App\Scopes\AttendanceScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperSecondaryAttendance
 */
class SecondaryAttendance extends Model
{
    use HasUuids, LogsActivity;

    protected $fillable = [
        'observations',
        'transfer_reason_id',
        'in_central_bed',
        'in_central_bed_updated_at',
        'protocol',
        'diagnostic_hypothesis',
        'unit_origin_id',
        'unit_destination_id',
        'complement_origin',
        'complement_destination',
        'requested_resource_id',
        'transfer_observation',
        'origin_unit_contact',
        'destination_unit_contact',
    ];

    public static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new AttendanceScope());
    }

    public function attendable(): MorphOne
    {
        return $this->morphOne(Attendance::class, 'attendable');
    }

    public function unitOrigin(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_origin_id');
    }

    public function unitDestination(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_destination_id');
    }

    public function requestedResource(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'requested_resource_id');
    }

    public function transferReason(): BelongsTo
    {
        return $this->belongsTo(TransferReason::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable();
    }
}
