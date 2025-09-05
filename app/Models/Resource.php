<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperResource
 */
class Resource extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
