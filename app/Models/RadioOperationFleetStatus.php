<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperRadioOperationFleetStatus
 */
class RadioOperationFleetStatus extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
