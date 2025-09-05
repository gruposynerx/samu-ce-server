<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperRequesterType
 */
class RequesterType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
