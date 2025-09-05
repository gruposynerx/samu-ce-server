<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperActionType
 */
class ActionType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
