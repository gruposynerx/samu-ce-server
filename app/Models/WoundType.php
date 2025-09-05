<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperWoundType
 */
class WoundType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
