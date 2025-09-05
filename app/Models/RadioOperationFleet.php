<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @mixin IdeHelperRadioOperationFleet
 */
class RadioOperationFleet extends Model
{
    use HasUuids, LogsActivity;

    protected $fillable = [
        'radio_operation_id',
        'vehicle_id',
        'status',
        'finished',
        'created_by',
    ];

    public function radioOperation(): BelongsTo
    {
        return $this->belongsTo(RadioOperation::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(RadioOperationFleetUser::class)
            ->withPivot('occupation_id');
    }

    public function externalProfessionals(): HasMany
    {
        return $this->hasMany(RadioOperationFleetUser::class)
            ->whereNotNull('external_professional');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'finished']);
    }
}
