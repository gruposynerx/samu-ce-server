<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperDistanceType
 */
class DistanceType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
