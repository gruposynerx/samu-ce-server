<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperWoundPlaceType
 */
class WoundPlaceType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
