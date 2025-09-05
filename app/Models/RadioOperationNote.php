<?php

namespace App\Models;

use App\Traits\HasUrcId;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperRadioOperationNote
 */
class RadioOperationNote extends Model
{
    use HasUrcId, HasUuids;

    protected $fillable = [
        'radio_operation_id',
        'datetime',
        'responsible_professional',
        'patrimony_id',
        'observation',
    ];

    public function patrimony(): BelongsTo
    {
        return $this->belongsTo(Patrimony::class);
    }

    public function radioOperation(): BelongsTo
    {
        return $this->belongsTo(RadioOperation::class);
    }
}
