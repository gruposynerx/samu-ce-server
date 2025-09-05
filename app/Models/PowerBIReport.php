<?php

namespace App\Models;

use App\Traits\HasUrcId;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin IdeHelperPowerBIReport
 */
class PowerBIReport extends Model
{
    use HasFactory, HasUrcId, HasUuids;

    protected $table = 'power_bi_reports';

    protected $fillable = [
        'urc_id',
        'name',
        'description',
        'url',
    ];

    public function urgencyRegulationCenter(): BelongsTo
    {
        return $this->belongsTo(UrgencyRegulationCenter::class, 'urc_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'power_bi_report_roles', 'power_bi_report_id', 'role_id')
            ->using(PowerBIReportRole::class);
    }
}
