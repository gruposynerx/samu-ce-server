<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperPlaceStatus
 */
class PlaceStatus extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
