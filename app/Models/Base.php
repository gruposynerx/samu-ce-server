<?php

namespace App\Models;

use App\Traits\HasUrcId;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Znck\Eloquent\Traits\BelongsToThrough;

/**
 * @mixin IdeHelperBase
 */
class Base extends Model
{
    use BelongsToThrough, HasFactory, HasUrcId, HasUuids;

    protected $fillable = [
        'urc_id',
        'unit_type_id',
        'city_id',
        'name',
        'national_health_registration',
        'street',
        'house_number',
        'zip_code',
        'neighborhood',
        'complement',
        'latitude',
        'longitude',
        'telephone',
        'company_registration_number',
        'company_name',
        'is_active',
        'vehicle_type_id',
        'regional_group_id',
    ];

    public function schedulesSchemas(): MorphMany
    {
        return $this->morphMany(UserScheduleSchema::class, 'schedulable');
    }

    public function userSchedules()
    {
        return $this->hasMany(UserSchedule::class, 'base_id', 'id');
    }

    public function scheduledUsers(): HasManyThrough
    {
        return $this->hasManyThrough(
            User::class,
            UserScheduleSchema::class,
            'schedulable_id',
            'id',
            'id',
            'user_id',
        );
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function unitType(): BelongsTo
    {
        return $this->belongsTo(UnitType::class, 'unit_type_id');
    }

    public function urgencyRegulationCenter(): BelongsTo
    {
        return $this->belongsTo(UrgencyRegulationCenter::class, 'urc_id');
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function regionalGroup(): BelongsTo
    {
        return $this->belongsTo(RegionalGroup::class);
    }

    public function attendances()
    {
        return $this->hasManyThrough(
            Attendance::class,
            VehicleStatusHistory::class,
            'base_id',
            'id',
            'id',
            'attendance_id',
        )->distinct('vehicle_status_histories.attendance_id');
    }
}
