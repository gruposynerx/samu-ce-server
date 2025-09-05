<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperFederalUnit
 */
class FederalUnit extends Model
{
    use HasFactory;

    protected $table = 'federal_units';

    protected $fillable = [
        'id',
        'name',
        'uf',
        'ibge_code',
        'slug',
    ];
}
