<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperBaseType
 */
class BaseType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
