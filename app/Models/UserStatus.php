<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperUserStatus
 */
class UserStatus extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
