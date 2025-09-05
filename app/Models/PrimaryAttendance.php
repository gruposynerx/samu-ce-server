<?php

namespace App\Models;

use App\Scopes\AttendanceScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @mixin IdeHelperPrimaryAttendance
 */
class PrimaryAttendance extends Model
{
    use HasUuids;

    protected $fillable = [
        'street',
        'house_number',
        'neighborhood',
        'reference_place',
        'primary_complaint',
        'observations',
        'distance_type_id',
        'location_type_id',
        'unit_destination_id',
        'in_central_bed',
        'in_central_bed_updated_at',
        'protocol',
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

    public function locationType(): BelongsTo
    {
        return $this->belongsTo(LocationType::class);
    }

    public function distanceType(): BelongsTo
    {
        return $this->belongsTo(DistanceType::class);
    }

    public function unitDestination(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_destination_id');
    }
}
