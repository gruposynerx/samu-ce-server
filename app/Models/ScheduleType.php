<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperScheduleType
 */
class ScheduleType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
    ];
}
