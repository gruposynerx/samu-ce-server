<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * @mixin IdeHelperRole
 */
class Role extends SpatieRole
{
    use HasFactory, HasUuids;

    public function avaliableFromUnit(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    public function urgencyRegulationCenter(): HasOneThrough
    {
        return $this->hasOneThrough(UrgencyRegulationCenter::class, UserRole::class, 'role_id', 'id', 'id', 'urc_id');
    }

    public function userLogs(): HasMany
    {
        return $this->hasMany(UserLog::class);
    }
}
