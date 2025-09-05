<?php

namespace App\Models;

use App\Observers\UrcObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Znck\Eloquent\Traits\BelongsToThrough;

/**
 * @mixin IdeHelperVehicle
 */
class Vehicle extends Model
{
    use BelongsToThrough, HasFactory, HasUuids, LogsActivity;

    protected $fillable = [
        'vehicle_type_id',
        'code',
        'license_plate',
        'base_id',
        'urc_id',
        'chassis',
        'general_availability',
        'tracking_device_imei',
        'tracking_system_id',
    ];

    protected $with = [
        'vehicleType',
        'base',
    ];

    protected $appends = [
        'description',
    ];

    public static function boot()
    {
        parent::boot();

        self::observe(new UrcObserver());

        static::creating(function (Vehicle $model) {
            if ($model->base_id) {
                $model->vehicle_type_id = $model->base->vehicle_type_id;
            }
        });

        static::updated(function (Vehicle $model) {
            if ($model->isDirty('base_id') && $model->latestVehicleStatusHistory) {
                $model->latestVehicleStatusHistory->replicate([
                    'base_id',
                    'city_id',
                    'vehicle_type_id',
                    'user_id',
                    'created_at',
                    'updated_at',
                ])->fill([
                    'base_id' => $model->base_id,
                    'city_id' => $model->base?->city_id,
                    'vehicle_type_id' => $model->base?->vehicle_type_id,
                    'user_id' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->save();
            }
        });
    }

    public function latestVehicleStatusHistory(): HasOne
    {
        return $this->hasOne(VehicleStatusHistory::class)->latest();
    }

    public function description(): Attribute
    {
        $vehicleTypeName = $this->vehicleType?->id ? $this->vehicleType->name : null;
        $vehicleCityName = $this->base?->id ? $this->base->city->name : 'Sem base';

        return Attribute::make(
            get: fn () => trim("$vehicleTypeName $this->code - $this->license_plate - $vehicleCityName"),
        );
    }

    public function base(): BelongsTo
    {
        return $this->belongsTo(Base::class)
            ->withoutGlobalScopes();
    }

    public function vehicleType()
    {
        return $this->belongsToThrough(
            VehicleType::class,
            Base::class,
            foreignKeyLookup: [
                Base::class => 'base_id',
                VehicleType::class => 'vehicle_type_id',
            ]
        );
    }

    public function urgencyRegulationCenter(): BelongsTo
    {
        return $this->belongsTo(UrgencyRegulationCenter::class, 'urc_id');
    }

    public function vehicleStatusHistory(): HasMany
    {
        return $this->hasMany(VehicleStatusHistory::class);
    }

    public function patrimonies(): HasMany
    {
        return $this->hasMany(Patrimony::class);
    }

    public function ableOccupations(): HasMany
    {
        return $this->hasMany(VehicleOccupation::class, 'vehicle_type_id', 'vehicle_type_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->logAll();
    }

    public function scopeAvailableForCurrentUrc(Builder $query): void
    {
        $query->where(function (Builder $query) {
            $query->orWhere(self::getTable() . '.general_availability', true)
                ->orWhereHas('base', fn ($q) => $q->where('bases.urc_id', auth()->user()->urc_id));
        });
    }

    public function mobileDevice(): HasOne
    {
        return $this->hasOne(MobileDevice::class);
    }
}
