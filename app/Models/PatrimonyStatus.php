<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperPatrimonyStatus
 */
class PatrimonyStatus extends Model
{
    protected $fillable = [
        'id',
        'name',
    ];

    public $timestamps = false;
}
