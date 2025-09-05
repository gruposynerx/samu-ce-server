<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperSceneRecordingConduct
 */
class SceneRecordingConduct extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'scene_recording_id',
        'conduct_id',
        'conduct_description',
    ];

    public function sceneRecording(): BelongsTo
    {
        return $this->belongsTo(SceneRecording::class);
    }

    public function conduct(): BelongsTo
    {
        return $this->belongsTo(Conduct::class);
    }
}
