<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @mixin IdeHelperCity
 */
class City extends Model
{
    use HasFactory;

    protected $table = 'cities';

    protected $fillable = [
        'id',
        'federal_unit_id',
        'name',
        'ibge_code',
        'telephone_code',
        'slug',
        'latitude',
        'longitude',
    ];

    public function federalUnit(): BelongsTo
    {
        return $this->belongsTo(FederalUnit::class, 'federal_unit_id', 'id');
    }

    public function vehicles(): HasManyThrough
    {
        return $this->hasManyThrough(Vehicle::class, Base::class, 'city_id', 'base_id', 'id', 'id');
    }
}
