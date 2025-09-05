<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperPeriodType
 */
class PeriodType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
