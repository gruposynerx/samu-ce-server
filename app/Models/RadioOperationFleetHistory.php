<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperRadioOperationFleetHistory
 */
class RadioOperationFleetHistory extends Model
{
    use HasUuids;

    protected $fillable = [
        'radio_operation_id',
        'fleet',
        'change_reason',
        'previous_fleet_creator',
        'created_by',
        'previous_vehicle_requested_at',
        'previous_vehicle_dispatched_at',
    ];

    protected $casts = [
        'fleet' => 'json',
    ];

    protected $appends = [
        'external_professionals',
    ];

    public function getExternalProfessionalsAttribute()
    {
        $usersCollect = collect($this->fleet['users']);

        return $usersCollect->whereNotNull('external_professional')->values();
    }

    public function radioOperation(): BelongsTo
    {
        return $this->belongsTo(RadioOperation::class);
    }

    public function previousFleetCreator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'previous_fleet_creator');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
