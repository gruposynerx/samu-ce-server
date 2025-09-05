<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperPatrimonyType
 */
class PatrimonyType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
