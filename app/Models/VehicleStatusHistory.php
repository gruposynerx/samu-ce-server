<?php

namespace App\Models;

use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperVehicleStatusHistory
 */
class VehicleStatusHistory extends Model
{
    protected $fillable = [
        'vehicle_id',
        'vehicle_status_id',
        'user_id',
        'attendance_id',
        'description',
        'vehicle_type_id',
        'base_id',
        'city_id',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::observe(new UserObserver());

        static::creating(function ($model) {
            $model->vehicle_type_id = $model->vehicle->vehicleType?->id;
            $model->base_id = $model->vehicle->base?->id;
            $model->city_id = $model->vehicle->base?->city?->id;
        });
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function vehicleStatus(): BelongsTo
    {
        return $this->belongsTo(VehicleStatus::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    public function vehicleBase(): BelongsTo
    {
        return $this->belongsTo(Base::class, 'base_id');
    }

    public function vehicleCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
