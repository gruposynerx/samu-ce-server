<?php

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin IdeHelperMobileDevice
 */
class MobileDevice extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'vehicle_id',
        'pin_id',
        'created_by',
        'is_active',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ActiveScope);

        static::updated(function (MobileDevice $mobileDevice) {
            $mobileDevice->history()->create([
                'vehicle_id' => $mobileDevice->vehicle_id ?? null,
                'base_id' => $mobileDevice->vehicle->base_id ?? null,
                'edited_by' => auth()->id(),
                'is_active' => $mobileDevice->is_active,
            ]);
        });
    }

    public function latestHistory(): HasOne
    {
        return $this->hasOne(MobileDeviceHistory::class)->latestOfMany();
    }

    public function history(): HasMany
    {
        return $this->hasMany(MobileDeviceHistory::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pin(): BelongsTo
    {
        return $this->belongsTo(Pin::class);
    }
}
