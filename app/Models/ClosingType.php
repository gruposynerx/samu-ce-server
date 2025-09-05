<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperClosingType
 */
class ClosingType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
