<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperPatient
 */
class Patient extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'gender_code',
        'age',
        'time_unit_id',
    ];

    public function timeUnit(): BelongsTo
    {
        return $this->belongsTo(TimeUnit::class);
    }
}
