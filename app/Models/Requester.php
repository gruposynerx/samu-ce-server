<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin IdeHelperRequester
 */
class Requester extends Model
{
    use HasUuids;

    protected $fillable = [
        'requester_type_id',
        'name',
        'primary_phone',
        'secondary_phone',
        'identifier',
        'council_number',
        'city_id',
    ];

    public function requesterType(): BelongsTo
    {
        return $this->belongsTo(RequesterType::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function ticket(): HasOne
    {
        return $this->hasOne(Ticket::class);
    }
}
