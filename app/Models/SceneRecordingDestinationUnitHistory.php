<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperSceneRecordingCounterreferral
 */
class SceneRecordingDestinationUnitHistory extends Model
{
    protected $fillable = [
        'scene_recording_id',
        'created_by',
        'unit_destination_id',
        'reason_id',
        'is_counter_reference',
        'destination_unit_contact',
    ];

    public static function boot(): void
    {
        parent::boot();

        self::creating(static function (self $model) {
            $model->created_by = auth()->user()->id;
        });
    }

    public function sceneRecording(): BelongsTo
    {
        return $this->belongsTo(SceneRecording::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function unitDestination(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_destination_id');
    }

    public function reason(): BelongsTo
    {
        return $this->belongsTo(CounterreferralReasonType::class, 'reason_id');
    }
}
