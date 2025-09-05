<?php

namespace App\Models\Sigtap;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperOccupation
 */
class Occupation extends Model
{
    use HasFactory;

    protected $table = 'occupations';

    protected $fillable = [
        'code',
        'name',
        'active',
    ];
}
