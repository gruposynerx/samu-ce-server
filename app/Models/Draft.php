<?php

namespace App\Models;

use App\Traits\HasUrcId;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Draft extends Model
{
    use HasUrcId, HasUuids;

    protected $fillable = [
        'type',
        'urc_id',
        'created_by',
        'fields',
    ];

    protected $casts = [
        'fields' => 'array',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (Draft $draft) {
            $draft->created_by = auth()->user()->id;
        });
    }

    public function urgencyRegulationCenter(): BelongsTo
    {
        return $this->belongsTo(UrgencyRegulationCenter::class, 'urc_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
