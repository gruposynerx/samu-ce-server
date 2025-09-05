<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperRespirationType
 */
class RespirationType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
