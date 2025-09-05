<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin IdeHelperUrgencyRegulationCenter
 */
class UrgencyRegulationCenter extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'city_id',
        'name',
        'street',
        'house_number',
        'neighborhood',
        'reference_place',
        'is_active',
    ];

    public function schedulesSchemas(): MorphMany
    {
        return $this->morphMany(UserScheduleSchema::class, 'schedulable');
    }

    public function city(): HasOne
    {
        return $this->hasOne(City::class, 'id', 'city_id');
    }

    public function users(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, UserRole::class, 'urc_id', 'id', 'id', 'user_id');
    }

    public function userRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'urc_id', 'role_id')
            ->withPivot('user_id')
            ->withTimestamps();
    }

    public function userLogs(): HasMany
    {
        return $this->hasMany(UserLog::class);
    }

    public function monitoringSetting(): HasOne
    {
        return $this->hasOne(MonitoringSetting::class, 'urc_id');
    }

    public function formsSetting(): HasOne
    {
        return $this->hasOne(FormSetting::class, 'urc_id');
    }
}
