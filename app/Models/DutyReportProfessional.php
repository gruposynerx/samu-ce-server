<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperDutyReportProfessional
 */
class DutyReportProfessional extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'duty_report_id',
        'user_id',
        'current_role_slug',
    ];

    public function dutyReport(): BelongsTo
    {
        return $this->belongsTo(DutyReport::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
