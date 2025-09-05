<?php

namespace App\Models;

use App\Traits\HasUrcId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlaceManagementHistory extends Model
{
    use HasFactory, HasUrcId;

    protected $fillable = [
        'place_id',
        'user_id',
        'urc_id',
    ];

    public function place(): BelongsTo
    {
        return $this->belongsTo(PlaceManagement::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function urgencyRegulationCenter(): BelongsTo
    {
        return $this->belongsTo(UrgencyRegulationCenter::class, 'urc_id');
    }
}
