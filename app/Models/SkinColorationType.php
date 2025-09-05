<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperSkinColorationType
 */
class SkinColorationType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
