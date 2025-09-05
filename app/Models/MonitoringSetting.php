<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperMonitoringSetting
 */
class MonitoringSetting extends Model
{
    protected $fillable = [
        'urc_id',
        'link_validation_time',
        'enable_attendance_monitoring',
    ];

    public $timestamps = false;

    public function urgencyRegulationCenter(): BelongsTo
    {
        return $this->belongsTo(UrgencyRegulationCenter::class, 'urc_id');
    }
}
