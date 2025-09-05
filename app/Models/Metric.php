<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @mixin IdeHelperMetric
 */
class Metric extends Model
{
    use HasUuids;

    protected $fillable = [
        'metricable_type',
        'metricable_id',
        'start_at',
        'diagnostic_evaluation',
        'systolic_blood_pressure',
        'diastolic_blood_pressure',
        'heart_rate',
        'respiratory_frequency',
        'temperature',
        'oxygen_saturation',
        'glasgow_scale',
    ];

    protected $casts = [
        'start_at' => 'datetime:Y-m-d\TH:i',
    ];

    public function metricable(): MorphTo
    {
        return $this->morphTo();
    }
}
