<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperLocationType
 */
class LocationType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
