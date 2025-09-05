<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperConsciousnessLevel
 */
class ConsciousnessLevel extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];
}
