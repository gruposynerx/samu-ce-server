<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperMobileDeviceHistory
 */
class MobileDeviceHistory extends Model
{
    protected $fillable = [
        'mobile_device_id',
        'vehicle_id',
        'base_id',
        'edited_by',
        'is_active',
        'device_mac_address',
    ];

    public function mobileDevice(): BelongsTo
    {
        return $this->brelongsTo(MobileDevice::class);
    }

    public function base(): BelongsTo
    {
        return $this->belongsTo(Base::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }
}
