<?php

namespace App\Models;

use App\Traits\HasUrcId;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperPatrimony
 */
class Patrimony extends Model
{
    use HasFactory, HasUrcId, HasUuids;

    protected $fillable = [
        'patrimony_type_id',
        'identifier',
        'patrimony_status_id',
        'vehicle_id',
        'urc_id',
    ];

    public function patrimonyType(): BelongsTo
    {
        return $this->belongsTo(PatrimonyType::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function urgencyRegulationCenter(): BelongsTo
    {
        return $this->belongsTo(UrgencyRegulationCenter::class, 'urc_id');
    }
}
