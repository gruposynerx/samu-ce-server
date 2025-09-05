<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin IdeHelperPin
 */
class Pin extends Model
{
    use HasUuids;

    protected $fillable = [
        'code',
        'device_mac_address',
    ];

    public function mobileDevice(): HasOne
    {
        return $this->hasOne(MobileDevice::class);
    }
}
