<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperPowerBIReportRole
 */
class PowerBIReportRole extends Pivot
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'power_bi_report_id',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function powerBIReport(): BelongsTo
    {
        return $this->belongsTo(PowerBIReport::class, 'power_bi_report_id');
    }
}
