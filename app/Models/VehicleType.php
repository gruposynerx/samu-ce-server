<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperVehicleType
 */
class VehicleType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
