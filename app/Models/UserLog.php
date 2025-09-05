<?php

namespace App\Models;

use App\Traits\HasUrcId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperUserLog
 */
class UserLog extends Model
{
    use HasFactory, HasUrcId;

    protected $fillable = [
        'name',
        'urc_id',
        'role_id',
        'logged_at',
        'user_agent',
        'longitude',
        'latitude',
        'user_log_login_type_id',
    ];

    public function urgencyRegulationCenter(): BelongsTo
    {
        return $this->BelongsTo(UrgencyRegulationCenter::class, 'urc_id');
    }

    public function role(): BelongsTo
    {
        return $this->BelongsTo(Role::class);
    }
}
