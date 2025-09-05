<?php

namespace App\Models\Sigtap;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperProcedure
 */
class Procedure extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'code',
        'code_9',
        'name',
        'complexity_type',
        'permitted_gender',
        'max_per_patient',
        'min_age',
        'max_age',
        'needs_age',
        'unit_value',
        'financing_code',
        'rubric',
        'active',
    ];
}
