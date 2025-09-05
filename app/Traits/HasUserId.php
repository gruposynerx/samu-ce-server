<?php

namespace App\Traits;

use App\Observers\UserObserver;

trait HasUserId
{
    public static function boot(): void
    {
        parent::boot();

        static::observe(new UserObserver());
    }
}
