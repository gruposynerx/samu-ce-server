<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperTransferReason
 */
class TransferReason extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
