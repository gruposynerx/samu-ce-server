<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperPriorityType
 */
class PriorityType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
