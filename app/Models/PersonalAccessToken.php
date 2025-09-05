<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

/**
 * @mixin IdeHelperPersonalAccessToken
 */
class PersonalAccessToken extends SanctumPersonalAccessToken
{
    use HasUuids;

    public static function boot()
    {
        parent::boot();

        static::deleting(function () {
            auth()->user()->update([
                'urc_id' => null,
                'current_role' => null,
            ]);
        });
    }
}
