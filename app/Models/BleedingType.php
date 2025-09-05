<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperBleedingType
 */
class BleedingType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
