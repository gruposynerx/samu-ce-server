<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperSweatingType
 */
class SweatingType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
