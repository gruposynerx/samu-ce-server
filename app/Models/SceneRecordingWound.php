<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperSceneRecordingWound
 */
class SceneRecordingWound extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'scene_recording_id',
        'wound_type_id',
        'wound_place_type_id',
    ];

    public function sceneRecording(): BelongsTo
    {
        return $this->belongsTo(SceneRecording::class);
    }

    public function woundType(): BelongsTo
    {
        return $this->belongsTo(WoundType::class);
    }

    public function woundPlaceType(): BelongsTo
    {
        return $this->belongsTo(WoundPlaceType::class);
    }
}
