<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperUserLogLoginType
 */
class UserLogLoginType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
