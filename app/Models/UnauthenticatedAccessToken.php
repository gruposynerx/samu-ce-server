<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperUnauthenticatedAccessToken
 */
class UnauthenticatedAccessToken extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'created_by',
        'token',
        'expires_at',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::creating(static function (UnauthenticatedAccessToken $model) {
            $model->created_by = auth()->id();
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
