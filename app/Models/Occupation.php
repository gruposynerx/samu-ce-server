<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperOccupation
 */
class Occupation extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
