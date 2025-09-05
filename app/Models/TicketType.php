<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperTicketType
 */
class TicketType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
