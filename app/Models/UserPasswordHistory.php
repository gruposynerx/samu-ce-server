<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperUserPasswordHistory
 */
class UserPasswordHistory extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'updated_by',
        'urc_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function urgencyRegulationCenter(): BelongsTo
    {
        return $this->belongsTo(UrgencyRegulationCenter::class, 'urc_id');
    }
}
