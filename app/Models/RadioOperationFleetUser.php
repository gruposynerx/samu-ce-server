<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperRadioOperationFleetUser
 */
class RadioOperationFleetUser extends Pivot
{
    use HasUuids;

    protected $fillable = [
        'radio_operation_fleet_id',
        'user_id',
        'occupation_id',
        'external_professional',
    ];
}
