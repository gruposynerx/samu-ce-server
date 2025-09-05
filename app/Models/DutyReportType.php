<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperDutyReportType
 */
class DutyReportType extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
