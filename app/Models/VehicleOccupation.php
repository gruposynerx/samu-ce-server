<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperVehicleOccupation
 */
class VehicleOccupation extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'vehicle_id',
        'occupation_id',
        'vehicle_type_id',
        'required',
        'identical',
        'active',
        'role_id',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function occupation(): BelongsTo
    {
        return $this->belongsTo(Occupation::class, 'occupation_id', 'code');
    }
}
