<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperConduct
 */
class Conduct extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
