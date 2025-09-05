<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperRegionalGroup
 */
class RegionalGroup extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'is_active',
    ];

    public function bases(): HasMany
    {
        return $this->hasMany(Base::class);
    }

    public function regionalGroupHistory(): HasMany
    {
        return $this->hasMany(RegionalGroupHistory::class);
    }

     public function userSchedules(): HasMany
    {
        return $this->hasMany(UserSchedule::class, 'regional_group_id');
    }
}
