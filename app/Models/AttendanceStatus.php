<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperAttendanceStatus
 */
class AttendanceStatus extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
