<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperTimeUnit
 */
class TimeUnit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
