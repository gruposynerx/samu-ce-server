<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperAntecedentType
 */
class AntecedentType extends Model
{
    public $timestamps = false;

    protected $table = 'antecedents_types';

    protected $fillable = [
        'name',
    ];
}
