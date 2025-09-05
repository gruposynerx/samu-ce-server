<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperVehicleMovementCode
 */
class VehicleMovementCode extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
