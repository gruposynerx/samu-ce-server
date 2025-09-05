<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperVehicleStatus
 */
class VehicleStatus extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
