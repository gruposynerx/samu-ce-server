<?php

namespace App\Models\Sigtap;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperIcd
 */
class Icd extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'procedure_code',
        'icd_code',
    ];
}
