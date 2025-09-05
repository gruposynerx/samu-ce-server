<?php

namespace App\Models;

use App\Traits\HasUrcId;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin IdeHelperDutyReport
 */
class DutyReport extends Model
{
    use HasUrcId, HasUuids;

    protected $fillable = [
        'urc_id',
        'created_by',
        'period_type_id',
        'internal_complications',
        'external_complications',
        'compliments',
        'events',
        'record_at',
        'duty_report_type_id',
    ];

    public static function boot(): void
    {
        parent::boot();

        self::creating(static function (DutyReport $model) {
            $model->created_by = auth()->user()->id;
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function professionals(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'duty_report_professionals', 'duty_report_id', 'user_id')
            ->withPivot('current_role_slug');
    }

    public function medicalRegulators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'duty_report_professionals', 'duty_report_id', 'user_id')
            ->withPivotValue('current_role_slug', 'medic');
    }

    public function tarms(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'duty_report_professionals', 'duty_report_id', 'user_id')
            ->withPivotValue('current_role_slug', 'TARM');
    }

    public function radioOperators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'duty_report_professionals', 'duty_report_id', 'user_id')
            ->withPivotValue('current_role_slug', 'radio-operator');
    }

    public function periodType(): BelongsTo
    {
        return $this->belongsTo(PeriodType::class);
    }

    public function dutyReportType(): BelongsTo
    {
        return $this->belongsTo(DutyReportType::class);
    }
}
