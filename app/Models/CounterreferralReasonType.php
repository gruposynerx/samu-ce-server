<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperCounterreferralReasonType
 */
class CounterreferralReasonType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
