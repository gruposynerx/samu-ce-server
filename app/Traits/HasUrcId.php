<?php

namespace App\Traits;

use App\Models\UrgencyRegulationCenter;
use App\Observers\UrcObserver;
use App\Scopes\UrcScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasUrcId
{
    public static function bootHasUrcId(): void
    {
        static::observe(new UrcObserver());
        static::addGlobalScope(new UrcScope());
    }

    public function urgencyRegulationCenter(): BelongsTo
    {
        return $this->belongsTo(UrgencyRegulationCenter::class, 'urc_id');
    }
}
